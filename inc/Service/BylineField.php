<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Assignment;
use GHOSTLABS\UNDERCARD\Entity\Candidate;
use GHOSTLABS\UNDERCARD\Entity\Credit;
use GHOSTLABS\UNDERCARD\Enum\AuthorRole;
use WP_Error;
use WP_Post;

final class BylineField
{
    public const FIELD = 'ghostlabs_undercard_byline';

    public function __construct(private readonly PostAuthors $postAuthors)
    {
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerField']);
    }

    public function registerField(): void
    {
        foreach ($this->postTypes() as $postType) {
            register_rest_field($postType, self::FIELD, [
                'get_callback'    => [$this, 'read'],
                'update_callback' => [$this, 'write'],
                'schema'          => $this->schema(),
            ]);
        }
    }

    public function postTypes(): array
    {
        
        return apply_filters('ghostlabs_undercard_byline_post_types', self::eligiblePostTypes());
    }

    public static function eligiblePostTypes(): array
    {
        return array_values(array_filter(
            get_post_types(['show_in_rest' => true, 'public' => true], 'names'),
            static fn(string $postType): bool => post_type_supports($postType, 'author'),
        ));
    }

    public function read(array $post): array
    {
        $postId = (int) ($post['id'] ?? 0);

        if ($postId <= 0) {
            return [];
        }

        return array_map(
            static fn(Credit $credit): array => Candidate::fromAuthor($credit->author)->toArray()
                + ['role' => $credit->role->value],
            $this->postAuthors->assignedFor($postId),
        );
    }

    public function write(mixed $value, WP_Post $post): bool|WP_Error
    {

        if (! current_user_can('edit_post', $post->ID)) {
            return new WP_Error(
                'ghostlabs_undercard_byline_forbidden',
                __('You are not allowed to change this byline.', 'ghostlabs-undercard'),
                ['status' => 403],
            );
        }

        $this->postAuthors->set((int) $post->ID, Assignment::listFrom($value));

        return true;
    }

    private function schema(): array
    {
        return [
            'description' => __('Who is credited on this post, in order.', 'ghostlabs-undercard'),
            'type'        => 'array',

            'context'     => ['edit'],
            'items'       => [
                'type'       => 'object',
                'properties' => [

                    'id'     => ['type' => 'string'],
                    'role'   => ['type' => 'string', 'enum' => array_column(AuthorRole::cases(), 'value')],
                    'name'   => ['type' => 'string'],
                    'kind'   => ['type' => 'string'],
                    'avatar' => ['type' => 'string'],
                ],
            ],
        ];
    }
}
