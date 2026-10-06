<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Asks the licensing service where the paid package lives.
 *
 * The same updater endpoint the installed paid edition polls, asked with the
 * same headers. Its `download_url` is complete and self-authorizing — a keyed
 * digest plus a single-window token — so it is handed to the installer
 * verbatim, never composed or rewritten here. On success the step's value is
 * that URL.
 */
final class PackageLocator
{
    public function locate(string $key): StepResult
    {
        $response = wp_remote_get(BackendEndpoints::packageUrl(), UpgradeFetch::args($key));

        if (is_wp_error($response)) {
            return StepResult::failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-undercard'));
        }

        $status      = (int) wp_remote_retrieve_response_code($response);
        $body        = json_decode(wp_remote_retrieve_body($response), true);
        $downloadUrl = is_array($body) && is_string($body['download_url'] ?? null) ? trim($body['download_url']) : '';

        if ($status === 200 && $downloadUrl !== '') {
            if (! self::isTrustedPackageUrl($downloadUrl)) {
                return StepResult::failure('download_untrusted', sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('The package location the licensing service returned was not recognized as a trusted address, so the download was refused. Download %s from your account page and install it manually.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                ));
            }

            return StepResult::success($downloadUrl);
        }

        $reason = is_array($body) && is_string($body['message'] ?? null) ? trim($body['message']) : '';

        if ($reason === '') {
            return StepResult::failure('download_unavailable', sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s is not available to download right now. Download it from your account page and install it manually.', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ));
        }

        return StepResult::failure('download_unavailable', sprintf(
            /* translators: 1: the paid edition's name. 2: the licensing service's own explanation. */
            __('%1$s is not available for this key: %2$s', 'ghostlabs-undercard'),
            TierMatrix::paidEditionName(),
            $reason
        ));
    }

    /**
     * 🔴 The plugin's one trust anchor for the code it is about to install.
     *
     * The package must come over https from the very host that answered this
     * request — the service serves both the updater and the ZIP from one
     * origin, so an off-host or downgraded URL is a spoofed or tampered
     * response, never a legitimate one. Compared against the endpoint class's
     * own host rather than a literal, so the release host rewrite retargets
     * this check with everything else.
     *
     * Userinfo is refused outright: `https://trusted@elsewhere/` parses to the
     * right-hand host, but is exactly the shape a spoof would take.
     */
    private static function isTrustedPackageUrl(string $url): bool
    {
        $parts = wp_parse_url($url);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $host = $parts['host'] ?? '';

        return $host !== '' && strcasecmp($host, BackendEndpoints::host()) === 0;
    }
}
