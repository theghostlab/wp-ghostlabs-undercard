<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

use GHOSTLABS\UNDERCARD\Config\UpgradeKeys;
use GHOSTLABS\UNDERCARD\Service\OptionsService;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The in-place upgrade, in order:
 *
 *   site check → lock → preflight → verify → save key → drop stale verdict
 *   → locate package → install → pending-notice handoff → install log
 *   → deactivate this edition → answer the activation URL
 *
 * Two rules shape the order. **Nothing is written before the key verifies** —
 * the first state change of any kind is saving a key the service has just
 * accepted (and `OptionsService::get()` is never called before that either, so
 * even a read cannot mint the row). **Nothing is torn down before the install
 * succeeds** — deactivating this edition is the last state change, so every
 * earlier failure leaves a working install behind.
 *
 * 🔴 **The paid edition is never activated from here.** Both editions' entry
 * files define the same `GHOSTLABS_UNDERCARD_*` constants, so the two must never
 * load in one PHP process: activating in-process would re-define them, bind the
 * activation to the wrong entry path, and leave the paid edition's code unloaded
 * for the rest of the request. This edition deactivates itself FIRST, then
 * answers core's nonce'd activation URL; the browser follows it, and the paid
 * edition activates in a fresh request where this one is no longer loaded.
 * `UpgradeOrchestratorTest` pins that order as a timeline.
 */
class UpgradeOrchestrator
{
    /** How long the concurrency lock is held before it self-heals. */
    private const LOCK_TTL = 5 * MINUTE_IN_SECONDS;

    /** How long the completion notice waits for the paid edition's first admin render. */
    private const PENDING_TTL = 15 * MINUTE_IN_SECONDS;

    /**
     * The option sub-key the paid edition reads the key from, in the one row
     * both editions share. A wire name, not ours to rename.
     */
    private const KEY_OPTION = 'licenseKey';

    private KeyVerifier $verifier;

    private PackageLocator $locator;

    private PackageInstallerInterface $installer;

    private OptionsService $options;

    public function __construct(
        ?KeyVerifier $verifier = null,
        ?PackageLocator $locator = null,
        ?PackageInstallerInterface $installer = null,
        ?OptionsService $options = null,
    ) {
        $this->verifier  = $verifier ?? new KeyVerifier();
        $this->locator   = $locator ?? new PackageLocator();
        $this->installer = $installer ?? new PackageInstaller();
        $this->options   = $options ?? new OptionsService();
    }

    public function run(string $key): UpgradeResult
    {
        $result = $this->attempt($key);

        // A security-relevant event whichever way it went: a success installs
        // and activates code; a failure may be a key or claim guess run through
        // this site. Emitted for an audit-log plugin to observe, because this
        // edition persists nothing of its own — no log table, by the
        // data-safety invariant. Never the key itself.
        do_action('ghostlabs_undercard_upgrade_event', [
            'ok'         => $result->ok,
            'stage'      => $result->stage,
            'noticeCode' => $result->noticeCode,
        ]);

        return $result;
    }

    private function attempt(string $key): UpgradeResult
    {
        // ⚠️ The sentence is NOT written here. `UpgradeSupport` is what the page
        // reads to decide whether to draw a key field at all and what the REST
        // gate answers a refused caller, so the offer and this refusal can never
        // disagree. The gate normally stops these cases first; this is depth.
        if (! UpgradeSupport::available()) {
            $code = UpgradeSupport::state() === UpgradeSupport::RESTRICTED ? 'restricted' : 'multisite';

            return UpgradeResult::fail('preflight', $code, UpgradeSupport::message());
        }

        // One upgrade at a time. Without this, two concurrent runs both read
        // "paid edition not yet installed", both call the installer, and the
        // loser reports a bogus failure while the winner has already swapped the
        // editions — an admin staring at a failure on a site with no active
        // edition.
        if (! $this->acquireLock()) {
            return UpgradeResult::fail('preflight', 'in_progress', __('An upgrade is already running on this site. Wait for it to finish, then reload this page.', 'ghostlabs-undercard'));
        }

        try {
            return $this->runLocked($key);
        } finally {
            delete_transient(UpgradeKeys::lockTransient());
        }
    }

    private function acquireLock(): bool
    {
        if (get_transient(UpgradeKeys::lockTransient())) {
            return false;
        }

        set_transient(UpgradeKeys::lockTransient(), 1, self::LOCK_TTL);

        return true;
    }

    private function runLocked(string $key): UpgradeResult
    {
        $blocked = $this->installer->preflight();

        if ($blocked !== null) {
            return UpgradeResult::failedAt('preflight', $blocked);
        }

        $verdict = $this->verifier->verify($key);

        if (! $verdict->ok) {
            return UpgradeResult::failedAt('verify', $verdict);
        }

        // The key is genuine: store it where the paid edition will look — the
        // one option row both editions share — and drop any verdict cached
        // before the purchase, so the paid edition re-verifies fresh on its
        // first request. Delete, never write.
        $this->options->save(self::KEY_OPTION, $key);

        delete_transient(UpgradeKeys::verdictTransient());

        $package = $this->locator->locate($key);

        if (! $package->ok || $package->value === null) {
            return UpgradeResult::failedAt('download', $package);
        }

        $install = $this->installer->install($package->value);

        if (! $install->ok) {
            return UpgradeResult::failedAt('install', $install);
        }

        if ($install->value === null || $install->value === '') {
            return UpgradeResult::fail('install', 'install_failed', __('The installer finished without reporting where the plugin went. Reload the Plugins screen to check, then try again.', 'ghostlabs-undercard'));
        }

        set_transient(UpgradeKeys::pendingTransient(), true, self::PENDING_TTL);

        // Fire-and-forget: the install log must not hold the response open, and
        // a lost ping costs nothing a customer can see.
        wp_remote_post(BackendEndpoints::installLogUrl(), UpgradeFetch::args($key) + [ 'blocking' => false ]);

        // 🔴 Deactivate BEFORE the activation URL exists. See the class docblock.
        $this->deactivateSelf();

        return UpgradeResult::complete($this->activationUrl($install->value));
    }

    /**
     * Non-silent, so this edition's own deactivation hooks run. Deactivating the
     * plugin this code belongs to is safe mid-request — its classes are already
     * loaded — and nothing after this point touches plugin state.
     *
     * Wrapped so a third-party `deactivate_plugin` hook that throws cannot turn a
     * completed install into a 500 that never delivers the activation URL. The
     * install has succeeded and the key is saved; the paid edition's entry file
     * deactivates this one on its first admin request regardless.
     */
    private function deactivateSelf(): void
    {
        if (! function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        try {
            deactivate_plugins(GHOSTLABS_UNDERCARD_PLUGIN_SLUG, false);
        } catch (\Throwable $e) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- a failed hook must leave a trace and must not break the handoff.
            error_log('[Undercard] a deactivation hook threw during the in-place upgrade: ' . $e->getMessage());
        }
    }

    /**
     * Core's own `plugins.php` activation URL, for the browser to follow.
     *
     * The basename comes from the installer, never a constant: the service's
     * package and a locally assembled build can unwrap to different directories.
     *
     * Composed with `add_query_arg` rather than `wp_nonce_url`: `wp_nonce_url`
     * entity-escapes ampersands for display, and this URL is consumed by
     * `location.assign`.
     */
    private function activationUrl(string $basename): string
    {
        $url = self_admin_url('plugins.php?action=activate&plugin=' . rawurlencode($basename));

        return add_query_arg('_wpnonce', wp_create_nonce('activate-plugin_' . $basename), $url);
    }
}
