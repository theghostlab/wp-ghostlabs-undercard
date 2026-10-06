<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Config\UpgradeKeys;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The `Plugin_Upgrader`-backed half of the upgrade: thin on purpose.
 *
 * Everything here is core machinery behaving as core behaves — which is why
 * the unit suite talks to the interface and this class is proven end to end
 * instead. Keep decisions out of this file; they belong in the orchestrator,
 * where the unit suite can see them.
 */
final class PackageInstaller implements PackageInstallerInterface
{
    /**
     * Directories a paid install can legitimately occupy — exactly one.
     *
     * The licensing service wraps the paid package in a tier-suffixed
     * directory, `ghostlabs-undercard-{tier}/`, which is also where `build.sh`
     * assembles a local paid build. ⚠️ **This edition's own directory,
     * `ghostlabs-undercard/`, is deliberately NOT on the list.** Listing it makes
     * the disk scan match the FREE install as "already installed", skip the
     * download, and hand back free's own basename to activate — after the run
     * has already deactivated it. `PackageInstallerTest` names that omission.
     *
     * ⚠️ Derived from `PluginConfig`, never written as a literal: a text-domain
     * rewrite retargets the literal domain string across an assembled tree, and a
     * sibling shipped a literal here once with exactly the effect above.
     *
     * @return list<string>
     */
    public static function paidDirectories(): array
    {
        return [ PluginConfig::getAppDomain() . '-' . UpgradeKeys::PAID_TIER ];
    }

    /**
     * The entry file every edition's build writes — the one this edition is
     * running from, derived for the same reason the directory is.
     */
    private static function entryFile(): string
    {
        return PluginConfig::updaterSlug() . '.php';
    }

    public function preflight(): ?StepResult
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        if (get_filesystem_method() !== 'direct' || WP_Filesystem() !== true) {
            return StepResult::failure('filesystem', sprintf(
                /* translators: %s: the paid edition's name. */
                __('This site cannot write to its plugins directory directly. Download %s from your account page and upload it from the Plugins screen.', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ));
        }

        return null;
    }

    public function install(string $zipUrl): StepResult
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $existing = $this->existingPaidBasename();

        if ($existing !== null) {
            return StepResult::success($existing);
        }

        $upgrader  = new \Plugin_Upgrader(new \Automatic_Upgrader_Skin());
        $installed = $upgrader->install($zipUrl);
        $basename  = $upgrader->plugin_info();

        if ($installed !== true || ! is_string($basename) || $basename === '') {
            return StepResult::failure('install_failed', sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s could not be installed. Download it from your account page and upload it from the Plugins screen.', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ));
        }

        return StepResult::success($basename);
    }

    /**
     * A paid build already on disk, from an earlier run that died between
     * install and activation. Detected by directory and entry file — reading an
     * inactive plugin's own constants would mean executing it.
     */
    private function existingPaidBasename(): ?string
    {
        foreach (array_keys(get_plugins()) as $key) {
            $basename = (string) $key;

            // Never this plugin itself, whatever directory it sits in.
            if ($basename === '' || $basename === GHOSTLABS_UNDERCARD_PLUGIN_SLUG) {
                continue;
            }

            if (! str_ends_with($basename, '/' . self::entryFile())) {
                continue;
            }

            [ $directory ] = explode('/', $basename, 2);

            if (in_array($directory, self::paidDirectories(), true)) {
                return $basename;
            }
        }

        return null;
    }
}
