<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Candidate;
use WP_User;

final class AuthorCandidates
{
    
    private const LIMIT = 20;

    public const MAX_LIMIT = 100;

    public function search(string $term = '', int $limit = self::LIMIT): array
    {
        $term  = trim($term);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        $candidates = [...$this->users($term, $limit), ...$this->guests($term, $limit)];

        usort(
            $candidates,
            static fn(Candidate $a, Candidate $b): int => strcasecmp($a->name, $b->name),
        );

        return array_slice($candidates, 0, $limit);
    }

    private function users(string $term, int $limit): array
    {
        $query = [
            'capability' => 'edit_posts',
            'orderby'    => 'display_name',
            'order'      => 'ASC',
            'number'     => $limit,
        ];

        if ($term !== '') {
            $query['search']         = '*' . $term . '*';
            $query['search_columns'] = ['display_name', 'user_login', 'user_nicename'];

            if (current_user_can('list_users')) {
                $query['search_columns'][] = 'user_email';
            }
        }

        $query = apply_filters('ghostlabs_undercard_author_candidates_query', $query, $term);

        $users = get_users($query);

        $candidates = [];

        foreach ($users as $user) {
            if (! $user instanceof WP_User) {
                continue;
            }

            $candidates[] = Candidate::user(
                (int) $user->ID,
                (string) $user->display_name,

                (string) get_avatar_url($user->ID),
            );
        }

        return $candidates;
    }

    private function guests(string $term, int $limit): array
    {
        $query = [
            'post_type'      => GuestAuthors::POST_TYPE,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
        ];

        if ($term !== '') {
            $query['s'] = $term;
        }

        $guests = get_posts($query);

        $candidates = [];

        foreach ($guests as $guest) {
            $slug = (string) $guest->post_name;

            if ($slug === '') {
                continue;
            }

            $candidates[] = Candidate::guest(
                $slug,
                (string) $guest->post_title,
                (string) get_the_post_thumbnail_url($guest->ID, 'thumbnail'),
            );
        }

        return $candidates;
    }
}
