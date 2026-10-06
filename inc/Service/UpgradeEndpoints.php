<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Every outward-facing URL this edition renders, in one place.
 *
 * **PHP assembles the whole URL; nothing else ever composes one.** The client
 * never appends a parameter, and no template writes a host. That is not tidiness:
 * a URL built in two halves loses whichever half the caller forgot, and the half
 * that goes missing first is the return contract — which fails invisibly, because
 * the link still renders, still resolves and still looks right.
 *
 * ## ⚠️ The base is a dev literal, whole and greppable
 *
 * `https://theghostlab.io` is rewritten at release by
 * `build.sh --marketing-base=https://…`, which is a **textual** transform. So the
 * base must stay one uninterrupted string literal: composing it from a scheme and
 * a host is the helper the rewrite would miss, and the result is a published
 * build pointing at a laptop.
 *
 * ⚠️ Do not "fix" it to the production origin. A finished production URL looks
 * correct and is worse: the rewrite would never match it, so every build —
 * development builds included — would point at production, and the release
 * assertion could not see the problem because there would be no dev literal left
 * to find. That exact defect shipped once in a sibling plugin.
 *
 * ⚠️ It is a **separate rewrite axis** from the backend host. The marketing site
 * and the service backend are different origins on different release cadences.
 * Since 2026-09-28 this edition talks to the backend too — the in-place upgrade,
 * whose origin is `Service\Upgrade\BackendEndpoints`, rewritten by
 * `--release-host` and asserted by `assert_no_dev_host()`.
 * `assert_no_dev_marketing_base()` is its sibling, not its replacement.
 *
 * ## The path is composed here, on purpose
 *
 * One flag rewrites the origin and every path rides on it, so a second URL never
 * needs a second literal — which is how the buy link in a sibling plugin became a
 * 404 that no release check could see.
 */
final class UpgradeEndpoints
{
    /**
     * The marketing site's origin. See the class docblock before touching it.
     */
    private const MARKETING_BASE = 'https://theghostlab.io';

    /** This plugin's page on the marketing site — the point of sale. */
    private const MARKETING_PATH = '/undercard';

    /**
     * The plugin's documentation article.
     *
     * ⚠️ **This slug must exist on the marketing site before the first release.**
     * It ships in the Plugins row of every install, and a 404 there is worse than
     * no link at all. It is listed in `_plans/upsell-surfaces-task-list.md` and in
     * the release skill's checklist for that reason. The short slug is the owner's
     * decision of 2026-09-19 (`_plans/sales-page-art-fixit-task-list.md` §3).
     */
    private const DOCS_PATH = '/docs/undercard';

    /**
     * The WordPress.org support forums, which is where a free plugin's support
     * goes — the forums are staffed by volunteers and do not cover commercial
     * plugins, so a paid edition must never link here.
     */
    private const SUPPORT_BASE = 'https://wordpress.org/support/plugin/';

    /**
     * Any page on the marketing site, off the one rewritten base.
     *
     * Exposed so nothing else in this edition writes a marketing URL of its own.
     */
    public static function marketingUrl(string $path): string
    {
        return self::MARKETING_BASE . $path;
    }

    public static function docsUrl(): string
    {
        return self::marketingUrl(self::DOCS_PATH);
    }

    /**
     * The plugin's forum on WordPress.org.
     *
     * ⚠️ **Derived from the text domain, which is the .org directory slug.**
     * `build.sh` refuses to assemble unless `ORG_SLUG` and the free entry
     * header's `Text Domain:` are equal — WordPress.org only delivers
     * translations when they are — so the domain is the slug, and reading it here
     * means the support link cannot drift from the directory the plugin actually
     * publishes under. A hand-written slug here sends support requests to
     * somebody else's plugin, and nothing on this side would say so.
     * `UpgradeEndpointsTest` closes the loop against `build.sh` itself.
     */
    public static function supportUrl(): string
    {
        return self::SUPPORT_BASE . PluginConfig::getAppDomain() . '/';
    }

    /**
     * The buy link for one surface, carrying the whole contract.
     *
     *     <base>/undercard?upgrade_return=<settings tab>&domain=<host>&from=<surface>
     *
     * `upgrade_return` and `domain` are the checkout-return contract: without
     * them the buyer pays and the site never finds out. They are addresses, never
     * secrets — the far side re-validates both before any redirect exists, so this
     * side stays a courier rather than a gatekeeper.
     *
     * ⚠️ **The source is appended after the contract, never in place of it.** The
     * failure worth guarding against is a parameter displacing another rather than
     * one going missing: a `from` that overwrote `domain` would still look like a
     * perfectly working link, and would still lose the purchase.
     *
     * ⚠️ The source is required, not optional. Every surface that asks for the
     * sale must name itself, and an optional parameter is one a call site can
     * forget — producing a real URL filed in a bucket nobody reads.
     */
    public static function upgradeUrl(UpsellSource $source): string
    {
        $query = [
            'upgrade_return=' . rawurlencode(self::returnUrl()),
            'domain=' . rawurlencode(self::domain()),
            // Not passed through rawurlencode: the case values are asserted
            // URL-safe in `UpsellSourceTest`, so what ships in the markup is the
            // same string a human reads in a report.
            'from=' . $source->value,
        ];

        return self::marketingUrl(self::MARKETING_PATH) . '?' . implode('&', $query);
    }

    /**
     * Where a buyer comes back to: this edition's settings screen.
     *
     * The settings screen rather than wherever they left from, and deliberately
     * the same address for all four surfaces — it is the one screen that can
     * carry a message about what to do next, and a return that landed on
     * `profile.php` would have nowhere to put one.
     *
     * 🔴 **`page=` and nothing else.** The sales backend's
     * `UpgradeReturnPolicy` accepts a return address whose query is exactly
     * `page=<this screen>` and refuses any other — a `&tab=upgrade` here made
     * every return fail validation, and the buyer landed on the success page
     * with no claim. The redirect it builds appends only the claim argument;
     * `Settings::currentTab()` opens the upgrade tab when that is present.
     */
    public static function returnUrl(): string
    {
        return admin_url(sprintf('options-general.php?page=%s', PluginConfig::$settingsMenuSlug));
    }

    /**
     * The site's host, which is what a purchase is bound to.
     */
    private static function domain(): string
    {
        return (string) wp_parse_url(get_site_url(), PHP_URL_HOST);
    }
}
