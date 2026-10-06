<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\RestRoute;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Service\Upgrade\ClaimExchanger;
use GHOSTLABS\UNDERCARD\Service\Upgrade\UpgradeOrchestrator;
use GHOSTLABS\UNDERCARD\Service\Upgrade\UpgradeResult;
use GHOSTLABS\UNDERCARD\Service\Upgrade\UpgradeSupport;
use GhostLabs\WPKit\Contracts\RestRouteInterface;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The one-shot upgrade endpoints, `POST {free-ns}/upgrade` and `/claim-upgrade`.
 *
 * Both answer the same shape — see `UpgradeResult`:
 *
 *     { ok, stage, noticeCode, message, activationUrl }
 *
 * **Domain failures are DATA, answered 200**; the page branches on
 * `noticeCode`. HTTP errors are reserved for a malformed request (core's 400,
 * from the args schema) and a refused caller (403, carrying the sentence).
 *
 * The routes live under THIS edition's namespace and vanish when the swap
 * succeeds — their whole job is to remove the edition serving them. The page
 * treats a mid-flight 404 as "already swapped" (a second tab, a double-click
 * that beat the deactivation), not as an error.
 *
 * Implements **wp-kit's** `RestRouteInterface`, not the local one: the local
 * `RouteRunner` constructs what it finds with no arguments, and these routes
 * must never be picked up by convention. `UpgradeRoutesProvider` registers them.
 */
final class UpgradeRoutes implements RestRouteInterface
{
    /**
     * 🔴 The query argument a buyer returns from checkout with, carrying a
     * single-use claim token.
     *
     * A contract with the licensing service's return policy, which appends
     * `{plugin}_upgrade_claim` for every plugin it knows — exactly as the
     * siblings' pages read theirs. Installed sites read this name, so it is
     * never changed retroactively. The page reads it; this class never does.
     */
    public const CLAIM_QUERY_ARG = 'undercard_upgrade_claim';

    /**
     * Longer than any key or claim the service mints; a payload past it is a
     * malformed request, refused before it ever reaches the network.
     */
    private const MAX_INPUT_LENGTH = 128;

    private UpgradeOrchestrator $orchestrator;

    private ClaimExchanger $claims;

    public function __construct(?UpgradeOrchestrator $orchestrator = null, ?ClaimExchanger $claims = null)
    {
        $this->orchestrator = $orchestrator ?? new UpgradeOrchestrator();
        $this->claims       = $claims ?? new ClaimExchanger();
    }

    /**
     * The paths the page hands `wp.apiFetch` — namespace-prefixed with a
     * leading slash and no `/wp-json`, the same form the settings form posts
     * `save-settings` to. Composed from the namespace the routes register
     * under, so the page and the routes cannot drift.
     *
     * @return array{upgrade: string, claim: string}
     */
    public static function paths(): array
    {
        return [
            'upgrade' => sprintf('/%s/upgrade', PluginConfig::restNameSpace()),
            'claim'   => sprintf('/%s/claim-upgrade', PluginConfig::restNameSpace()),
        ];
    }

    public function routes(): void
    {
        register_rest_route(PluginConfig::restNameSpace(), '/upgrade', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'upgrade' ],
                'permission_callback' => [ self::class, 'permission' ],
                'args'                => [
                    'licenseKey' => self::inputSchema(),
                ],
            ],
        ]);

        register_rest_route(PluginConfig::restNameSpace(), '/claim-upgrade', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'claimUpgrade' ],
                'permission_callback' => [ self::class, 'permission' ],
                'args'                => [
                    'claimToken' => self::inputSchema(),
                ],
            ],
        ]);
    }

    /**
     * The strictest gate in the plugin, on both routes, and the same answer the
     * page reads before it draws the offer: `UpgradeSupport::canInstall()`.
     *
     * ⚠️ **A refusal has to say something.** Bare `false` renders core's generic
     * 403 with nothing in it, which on a network was all a subsite Administrator
     * ever saw.
     */
    public static function permission(): bool|WP_Error
    {
        if (UpgradeSupport::canInstall()) {
            return true;
        }

        return new WP_Error('rest_forbidden', UpgradeSupport::message(), [ 'status' => 403 ]);
    }

    public function upgrade(WP_REST_Request $request): WP_REST_Response|WP_HTTP_Response
    {
        return $this->respond($this->orchestrator->run(self::input($request, 'licenseKey')));
    }

    /**
     * The checkout-return path: redeem the claim server-side, then continue as
     * the pasted-key path — the exchanged key goes through the same
     * orchestrator, so every guarantee pinned on `/upgrade` holds here by
     * construction.
     */
    public function claimUpgrade(WP_REST_Request $request): WP_REST_Response|WP_HTTP_Response
    {
        $claim = $this->claims->exchange(self::input($request, 'claimToken'));

        if (! $claim->ok || $claim->value === null) {
            return $this->respond(UpgradeResult::failedAt('claim', $claim));
        }

        return $this->respond($this->orchestrator->run($claim->value));
    }

    private function respond(UpgradeResult $result): WP_REST_Response|WP_HTTP_Response
    {
        return rest_ensure_response($result->toArray());
    }

    /** The already-validated, already-sanitized parameter. */
    private static function input(WP_REST_Request $request, string $name): string
    {
        $value = $request->get_param($name);

        return is_string($value) ? $value : '';
    }

    /**
     * One schema for both inputs: a required, bounded, sanitized string. Core
     * validates before it sanitizes and answers a missing or failing parameter
     * with its own 400 before the callback runs.
     *
     * @return array<string, mixed>
     */
    private static function inputSchema(): array
    {
        return [
            'required'          => true,
            'type'              => 'string',
            'validate_callback' => static fn (mixed $value): bool => is_string($value)
                && trim($value) !== ''
                && strlen($value) <= self::MAX_INPUT_LENGTH,
            'sanitize_callback' => static fn (mixed $value): string => sanitize_text_field(is_string($value) ? $value : ''),
        ];
    }
}
