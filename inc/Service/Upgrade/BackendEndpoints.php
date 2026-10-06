<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Every licensing-service URL the in-place upgrade talks to, in one place.
 *
 * Not to be confused with `Service\UpgradeEndpoints`, which assembles the
 * MARKETING links (the buy link, the checkout return, docs). That is a
 * different origin on a different release axis — `--marketing-base` — and the
 * two are kept in separate classes so each rewrite has exactly one file to find.
 *
 * ## ⚠️ The host is a dev literal, whole and greppable
 *
 * `https://enter.theghostlab.io` is rewritten at release by
 * `build.sh --release-host=https://…`, a **textual** transform over the
 * assembled tree, which then asserts no dev host survived. So the host must stay
 * one uninterrupted string literal — the same literal the paid edition's own
 * endpoint class carries. Composing it from pieces is the helper the rewrite
 * would miss; "fixing" it to the production origin would make every build,
 * development builds included, talk to production, with no dev literal left for
 * the release assertion to find.
 *
 * The route paths are the service's own names and are not ours to choose.
 */
final class BackendEndpoints
{
    /** The authenticated half: every call here bears the key. */
    private const SERVICE_URL = 'https://enter.theghostlab.io/_service';

    /**
     * The public half, where a checkout-return claim is redeemed — public
     * because the caller's whole problem is that it holds no key yet.
     */
    private const PUBLIC_URL = 'https://enter.theghostlab.io';

    /** The one-shot verdict: is this key good for the paid edition, on this site? */
    public static function verdictUrl(): string
    {
        return self::SERVICE_URL . '/check-license';
    }

    /**
     * Where the paid package lives: the same updater endpoint the installed
     * paid edition polls, answering a self-authorizing `download_url`.
     */
    public static function packageUrl(): string
    {
        return self::SERVICE_URL . '/plugin-updater';
    }

    /** The install log, pinged fire-and-forget once the package is on disk. */
    public static function installLogUrl(): string
    {
        return self::SERVICE_URL . '/plugin-installed';
    }

    /** The claim exchange: a checkout's claim token in, the key out. */
    public static function claimUrl(): string
    {
        return self::PUBLIC_URL . '/claim-license';
    }

    /**
     * The backend's host, which a package URL must be served from.
     *
     * Read off the literal rather than restated, so the release rewrite
     * retargets the origin check along with everything else.
     */
    public static function host(): string
    {
        return (string) wp_parse_url(self::SERVICE_URL, PHP_URL_HOST);
    }
}
