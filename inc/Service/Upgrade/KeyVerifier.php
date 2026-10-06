<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The upgrade's own key check — a plain `wp_remote_post` client, GPL, written
 * for this edition.
 *
 * It caches nothing, on purpose: it runs once, on an explicit admin action, and
 * verdict caching belongs to the edition being installed. The check also counts
 * this site against the key on the service's side, so the paid edition's first
 * check after the swap finds the domain already registered.
 */
final class KeyVerifier
{
    /** Verdict codes that mean "renew": 17 subscription canceled, 83 expired. */
    private const RENEWAL_CODES = [ 17, 83 ];

    /**
     * Statuses the service's auth layer answers for a bearer it does not know —
     * the verdict is never reached, so there is no body worth reading.
     */
    private const UNRECOGNIZED_STATUSES = [ 401, 403, 404 ];

    public function verify(string $key): StepResult
    {
        $response = wp_remote_post(BackendEndpoints::verdictUrl(), UpgradeFetch::args($key));

        if (is_wp_error($response)) {
            return StepResult::failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-undercard'));
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        if (in_array($status, self::UNRECOGNIZED_STATUSES, true)) {
            return self::unrecognized();
        }

        $verdict = json_decode(wp_remote_retrieve_body($response), true);

        if ($status !== 200 || ! is_array($verdict)) {
            return StepResult::failure('network', __('The licensing service answered unexpectedly. Please try again in a few minutes.', 'ghostlabs-undercard'));
        }

        // Exactly `true`: a truthy string is not a verdict.
        if (($verdict['valid'] ?? false) === true) {
            return StepResult::success();
        }

        $code = is_numeric($verdict['code'] ?? null) ? (int) $verdict['code'] : 0;

        if (in_array($code, self::RENEWAL_CODES, true)) {
            return StepResult::failure('renewal', __('This key has expired or its subscription was canceled. Renew it to upgrade.', 'ghostlabs-undercard'));
        }

        if ($code === 404) {
            return self::unrecognized();
        }

        $reason = is_string($verdict['message'] ?? null) ? trim($verdict['message']) : '';

        if ($reason === '') {
            return StepResult::failure('key_refused', __('This key cannot be used to upgrade this site.', 'ghostlabs-undercard'));
        }

        return StepResult::failure('key_refused', sprintf(
            /* translators: %s: the licensing service's own explanation. */
            __('This key cannot be used to upgrade this site: %s', 'ghostlabs-undercard'),
            $reason
        ));
    }

    private static function unrecognized(): StepResult
    {
        return StepResult::failure('key_not_recognized', __('That key was not recognized. Check it against your receipt email.', 'ghostlabs-undercard'));
    }
}
