<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Can this screen offer the in-place upgrade, and if not, what does this caller
 * need to be told?
 *
 * **One answer, read by everything that either OFFERS the upgrade or PERFORMS
 * it**: the settings page (which decides whether to draw a key field at all),
 * the REST gate (which answers a refused caller) and the orchestrator (which
 * refuses again, in depth). Every sibling that decided the offer and the refusal
 * in different files had them disagree — the screen took a key on a network, and
 * the refusal arrived afterward, to somebody who had usually already paid. The
 * portfolio rule is to **refuse before the offer is shown**.
 *
 * ⚠️ **Availability is a property of the SITE; capability is a property of the
 * USER.** Both are asked, separately. A super admin on a network can install
 * plugins and still cannot use the in-place swap, and the reason is the
 * ACTIVATION half: the run finishes by handing the browser a per-blog
 * `plugins.php?action=activate` URL, so on a network it would take this edition
 * down everywhere and bring the paid one up on exactly one subsite.
 *
 * ⚠️ **Four refusals, because four different people hit this.** A subsite
 * Administrator is denied `install_plugins` by core on every network, so telling
 * them to "install it from the network admin" names a screen they cannot open.
 */
final class UpgradeSupport
{
    /** In place, here, now. The only state that renders a key field. */
    public const AVAILABLE = 'available';

    /** A network, and this caller could install it themselves. */
    public const NETWORK_ADMIN = 'network_admin';

    /** A network, and installing is somebody else's job. */
    public const NETWORK_DELEGATED = 'network_delegated';

    /** File modification is switched off — DISALLOW_FILE_MODS, or a host filter. */
    public const RESTRICTED = 'restricted';

    /** Nothing is switched off; this caller simply may not install plugins. */
    public const NO_PERMISSION = 'no_permission';

    /**
     * The site cannot write its plugins directory directly (it would need FTP or
     * SSH credentials), which the installer refuses — so the offer is refused
     * first, before it takes a key or spends a claim.
     */
    public const FILESYSTEM = 'filesystem';

    /**
     * 🔴 The one question the page and the REST gate both ask: may THIS caller
     * run the in-place upgrade on THIS site, right now?
     *
     * The site must support it and the caller must hold what core itself demands
     * to install and activate a plugin, on top of the settings screen's own
     * capability. The page draws the key field only when this is true, and
     * renders `message()` in its place otherwise.
     */
    public static function canInstall(): bool
    {
        return self::state() === self::AVAILABLE;
    }

    /** Whether the in-place swap can run on this site at all, whoever is asking. */
    public static function available(): bool
    {
        return wp_is_file_mod_allowed('can_install_plugins') && ! is_multisite();
    }

    /**
     * ⚠️ **File modification is asked about FIRST, and before multisite.** Core
     * denies `install_plugins` for DISALLOW_FILE_MODS *before* it reaches its
     * multisite branch, so on a locked-down network a capability check alone
     * cannot tell a super admin from a subsite Administrator — and the delegated
     * sentence would then tell the one person who could act to go and ask a
     * network administrator, which is themselves.
     *
     * Read through `wp_is_file_mod_allowed()` rather than the constant: it is
     * core's own seam, and a host can filter it.
     */
    public static function state(): string
    {
        if (! wp_is_file_mod_allowed('can_install_plugins')) {
            return self::RESTRICTED;
        }

        if (is_multisite()) {
            return self::callerCanInstall() ? self::NETWORK_ADMIN : self::NETWORK_DELEGATED;
        }

        if (! self::callerCanInstall()) {
            return self::NO_PERMISSION;
        }

        return self::writesDirectly() ? self::AVAILABLE : self::FILESYSTEM;
    }

    /**
     * What to show in place of the offer, or `''` when it can be offered.
     *
     * ⚠️ Resolved at call time and never memoized: this is reachable from a page
     * render and from a REST request, and a static would bake in whichever
     * locale registered first.
     */
    public static function message(): string
    {
        switch (self::state()) {
            case self::NETWORK_ADMIN:
                return sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('The in-place upgrade doesn\'t run on multisite networks. Download %s from your account page and install it from the network admin — you\'ll enter your license key on each site.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                );

            case self::NETWORK_DELEGATED:
                return sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('Installing plugins on this network is done by a network administrator. Ask them to install %s — your bylines, guests and profile fields stay exactly as they are.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                );

            case self::RESTRICTED:
                return sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('Installing plugins is turned off on this site. Ask whoever manages it to install %s — your bylines, guests and profile fields stay exactly as they are.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                );

            case self::FILESYSTEM:
                return sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('This site cannot write to its plugins directory directly. Download %s from your account page and upload it from the Plugins screen.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                );

            case self::NO_PERMISSION:
                return sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('You don\'t have permission to install plugins on this site. Ask whoever manages it to install %s — your bylines, guests and profile fields stay exactly as they are.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                );
        }

        return '';
    }

    /**
     * All three answers at once, for a page that wants them together.
     *
     * @return array{available: bool, state: string, message: string}
     */
    public static function config(): array
    {
        return [
            'available' => self::canInstall(),
            'state'     => self::state(),
            'message'   => self::message(),
        ];
    }

    /** The capabilities core itself demands to install and activate a plugin. */
    /**
     * The same question `PackageInstaller::preflight()` asks, asked here too so
     * the offer is never drawn where the install would be refused. Outside
     * wp-admin (a REST request), `get_filesystem_method()` is not loaded yet.
     */
    private static function writesDirectly(): bool
    {
        if (! function_exists('get_filesystem_method')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        return get_filesystem_method() === 'direct';
    }

    private static function callerCanInstall(): bool
    {
        return current_user_can('manage_options')
            && current_user_can('install_plugins')
            && current_user_can('activate_plugins');
    }
}
