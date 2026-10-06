<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\RestRoute;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Entity\Candidate;
use GHOSTLABS\UNDERCARD\Service\AuthorCandidates;
use WP_REST_Request;
use WP_REST_Response;

final class AuthorRoutes implements RestRouteInterface
{
    public function __construct(
        private readonly AuthorCandidates $candidates = new AuthorCandidates(),
    ) {
    }

    public function routes(): void
    {
        register_rest_route(
            PluginConfig::restNameSpace(),
            '/authors',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'search'],
                'permission_callback' => [$this, 'canSearch'],
                'args'                => [
                    'search'   => [
                        'type'              => 'string',
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],

                    'per_page' => [
                        'type'              => 'integer',
                        'default'           => 20,
                        'minimum'           => 1,
                        'maximum'           => AuthorCandidates::MAX_LIMIT,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                    ],
                ],
            ],
        );
    }

    public function canSearch(): bool
    {
        return current_user_can('edit_posts');
    }

    public function search(WP_REST_Request $request): WP_REST_Response
    {
        $found = $this->candidates->search(
            (string) $request->get_param('search'),
            (int) $request->get_param('per_page'),
        );

        return new WP_REST_Response(array_map(
            static fn(Candidate $candidate): array => $candidate->toArray(),
            $found,
        ));
    }
}
