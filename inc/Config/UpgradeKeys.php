<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Config;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The storage names this edition shares with the edition it upgrades into.
 *
 * Only one edition runs on each side of the swap, so the two can never compare
 * notes at runtime: **the name is the contract.** Each is pinned as a literal by
 * this edition's `UpgradeKeysTest` and again by the paid edition's own suite,
 * because two suites pinned to the same bytes is the only way to hold equal two
 * files that never load together.
 *
 * ⚠️ Composed from `PluginConfig`'s two halves, never written whole. The
 * hyphenated domain-slug is exactly the string a text-domain rewrite retargets,
 * and a sibling lost its upgrade to that once; the halves are what such a
 * rewrite cannot reach.
 */
final class UpgradeKeys
{
    /**
     * The paid edition's tier token — the one being installed, never this one.
     *
     * The single declaration of it in this edition: the directory the paid
     * package unwraps to, the verdict cache below and the tier header the
     * service requires all read it here. `PluginConfig::tier()` answers `free`
     * in this build, which names nothing the paid edition owns.
     */
    public const PAID_TIER = 'pro';

    /**
     * Where the paid edition caches its verdict on the stored key.
     *
     * The upgrade DELETES it once the key verifies — a verdict cached before the
     * purchase would otherwise gate the paid edition off until it expired — and
     * never writes it: the cached shape is the paid edition's business.
     */
    public static function verdictTransient(): string
    {
        return sprintf('%s-%s-%s', PluginConfig::$domain, PluginConfig::$slug, self::PAID_TIER);
    }

    /**
     * The one-shot "upgrade complete" flag.
     *
     * This edition sets it as its last write before handing the browser to the
     * paid edition's activation URL; the paid edition consumes it on its first
     * admin render. By then this edition is deactivated, so confirming that the
     * upgrade landed is necessarily the other side's job.
     */
    public static function pendingTransient(): string
    {
        return sprintf('_%s_%s_upgrade_pending', PluginConfig::$domain, PluginConfig::$slug);
    }

    /**
     * The short-lived lock that keeps two runs — a double-click, a second tab,
     * a retrying proxy — from both driving the installer. It self-heals through
     * its TTL if a run dies mid-flight. This edition's alone.
     */
    public static function lockTransient(): string
    {
        return sprintf('_%s_%s_upgrade_lock', PluginConfig::$domain, PluginConfig::$slug);
    }
}
