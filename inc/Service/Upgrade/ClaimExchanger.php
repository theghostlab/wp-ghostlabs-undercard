<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Redeems a checkout's claim token for the key, server to server.
 *
 * Only the claim ever rode the browser redirect; only this call carries the
 * key back. It is the plugin's one unauthenticated outbound call — there is no
 * key to bear yet, which is why the claim route sits outside the service's
 * authenticated half. On success the step's value is the key, which then goes
 * through the same orchestrator as a pasted one.
 *
 * Every refusal collapses into one recovery — paste the key from the receipt
 * email — because the service deliberately answers unknown, expired, spent and
 * wrong-domain claims identically.
 */
final class ClaimExchanger
{
    public function exchange(string $claimToken): StepResult
    {
        $response = wp_remote_post(BackendEndpoints::claimUrl(), UpgradeFetch::claimArgs($claimToken));

        if (is_wp_error($response)) {
            return StepResult::failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-undercard'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $key  = is_array($body) && is_string($body['licenseKey'] ?? null) ? trim($body['licenseKey']) : '';

        if ((int) wp_remote_retrieve_response_code($response) === 200 && $key !== '') {
            return StepResult::success($key);
        }

        return StepResult::failure('claim_gone', __('This upgrade link has expired or was already used. Paste the license key from your receipt email instead.', 'ghostlabs-undercard'));
    }
}
