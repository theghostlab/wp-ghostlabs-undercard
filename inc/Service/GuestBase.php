<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\SettingsCopy;
use WP_Post;
use WP_Post_Type;
use WP_Rewrite;

final class GuestBase
{
    public const SCOPE_ALL  = 'all';
    public const SCOPE_CORE = 'core';

    public static function normalize(string $raw): string
    {
        $slug = sanitize_title($raw);

        return $slug === '' ? SiteSettings::DEFAULT_GUEST_SLUG : $slug;
    }

    public static function owner(string $slug, string $scope = self::SCOPE_ALL): ?string
    {
        $slug = self::firstSegment($slug);

        if ($slug === '') {
            return null;
        }

        if (in_array($slug, self::coreBases(), true)) {
            return SettingsCopy::guestBaseOwnerCore();
        }

        if ($scope === self::SCOPE_CORE) {
            return null;
        }

        foreach (get_post_types(['public' => true], 'objects') as $type) {
            if ($type->name === GuestAuthors::POST_TYPE) {
                continue;
            }

            if (in_array($slug, self::postTypeBases($type), true)) {
                return SettingsCopy::guestBaseOwnerPostType($type->label);
            }
        }

        foreach (get_taxonomies(['public' => true], 'objects') as $taxonomy) {
            $base = is_array($taxonomy->rewrite) ? self::firstSegment((string) ($taxonomy->rewrite['slug'] ?? '')) : '';

            if ($base === $slug) {
                return SettingsCopy::guestBaseOwnerTaxonomy($taxonomy->label);
            }
        }

        $page = get_page_by_path($slug);

        if ($page instanceof WP_Post && $page->post_status === 'publish') {
            return SettingsCopy::guestBaseOwnerPage($page->post_title);
        }

        return null;
    }

    private static function coreBases(): array
    {
        $rewrite = $GLOBALS['wp_rewrite'] ?? null;

        $bases = [
            'wp-admin', 'wp-content', 'wp-includes', 'embed', 'trackback', 'attachment', 'feed',
            rest_get_url_prefix(),
        ];

        if ($rewrite instanceof WP_Rewrite) {
            $bases[] = $rewrite->author_base;
            $bases[] = $rewrite->search_base;
            $bases[] = $rewrite->comments_base;
            $bases[] = $rewrite->pagination_base;
            $bases[] = $rewrite->comments_pagination_base;
            $bases[] = $rewrite->feed_base;
        } else {
            array_push($bases, 'author', 'search', 'comments', 'page', 'comment-page');
        }

        $category = get_option('category_base');
        $tag      = get_option('tag_base');
        $bases[]  = is_string($category) && $category !== '' ? $category : 'category';
        $bases[]  = is_string($tag) && $tag !== '' ? $tag : 'tag';

        return array_values(array_filter(array_map(self::firstSegment(...), $bases)));
    }

    private static function postTypeBases(WP_Post_Type $type): array
    {
        $bases = [];

        if (is_array($type->rewrite) && isset($type->rewrite['slug'])) {
            $bases[] = self::firstSegment((string) $type->rewrite['slug']);
        }

        if (is_string($type->has_archive)) {
            $bases[] = self::firstSegment($type->has_archive);
        }

        return array_values(array_filter($bases));
    }

    private static function firstSegment(string $path): string
    {
        $path = trim($path, '/ ');
        $cut  = strpos($path, '/');

        return strtolower($cut === false ? $path : substr($path, 0, $cut));
    }
}
