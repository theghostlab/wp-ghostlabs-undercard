<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Config\UpgradeKeys;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The HTTP arguments every licensing-service call shares.
 *
 * The service authenticates on the bearer and routes on the domain, slug and
 * tier headers — and refuses outright a call with any of them empty. The slug
 * is the INTERNAL one (`PluginConfig::$slug`), exactly what the paid edition's
 * own requests send, never the WordPress.org directory name.
 *
 * ⚠️ The tier header names the paid tier, never `PluginConfig::tier()`: this
 * caller is asking about the entitlement and the package it wants to INSTALL,
 * not about itself, and `tier()` answers `free` here.
 *
 * The development-only TLS relaxation for the self-signed dev backend lives
 * here once, for every call the client makes, inside a release fence.
 */
final class UpgradeFetch
{
    /**
     * Arguments for a call that bears the key.
     *
     * @return array{timeout: int, headers: array<string, string>, sslverify?: bool}
     */
    public static function args(string $key): array
    {
        $args = self::base();

        $args['headers'] = [
            'Authorization' => 'Bearer ' . self::headerSafe($key),
            'Domain'        => self::domain(),
            'Plugin-Slug'   => PluginConfig::$slug,
            'Plugin-Tier'   => UpgradeKeys::PAID_TIER,
        ];

        return $args;
    }

    /**
     * Arguments for the claim exchange: no bearer — there is no key yet, which
     * is the whole reason the claim exists — and the token in a JSON body
     * rather than a URL, where it would land in logs.
     *
     * @return array{timeout: int, headers: array<string, string>, body: string, sslverify?: bool}
     */
    public static function claimArgs(string $claimToken): array
    {
        $args = self::base();

        $args['headers'] = [
            'Domain'       => self::domain(),
            'Plugin-Slug'  => PluginConfig::$slug,
            'Content-Type' => 'application/json',
        ];

        $args['body'] = (string) wp_json_encode([ 'token' => $claimToken ]);

        return $args;
    }

    /**
     * @return array{timeout: int, sslverify?: bool}
     */
    private static function base(): array
    {
        return [
            'timeout' => 45,
        ];
    }

    /** The site a purchase is bound to — a bare host, as the service expects. */
    private static function domain(): string
    {
        $host = wp_parse_url(get_site_url(), PHP_URL_HOST);

        return is_string($host) ? $host : '';
    }

    /**
     * A value that cannot become a second header. The REST route already
     * sanitizes a pasted key, but a claimed key arrives from the network, and
     * stripping here makes the guarantee local rather than inherited.
     */
    private static function headerSafe(string $value): string
    {
        return str_replace([ "\r", "\n" ], '', $value);
    }
}
