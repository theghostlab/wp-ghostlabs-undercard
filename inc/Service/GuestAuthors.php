<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\ProfileFields;
use GHOSTLABS\UNDERCARD\Entity\Author;
use InvalidArgumentException;
use WP_Post;

final class GuestAuthors implements GuestAuthorsInterface
{
    
    public const POST_TYPE = 'ghlbsuc_guest';

    private const REWRITE_BASE_OPTION = 'ghostlabs_undercard_guest_rewrite_base';

    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerMeta']);
    }

    public function registerPostType(): void
    {
        $slug = (string) apply_filters('ghostlabs_undercard_guest_slug', SiteSettings::DEFAULT_GUEST_SLUG);

        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'               => __('Guest Authors', 'ghostlabs-undercard'),
                'singular_name'      => __('Guest Author', 'ghostlabs-undercard'),
                'add_new_item'       => __('Add Guest Author', 'ghostlabs-undercard'),
                'edit_item'          => __('Edit Guest Author', 'ghostlabs-undercard'),
                'search_items'       => __('Search Guest Authors', 'ghostlabs-undercard'),
                'not_found'          => __('No guest authors yet.', 'ghostlabs-undercard'),
                'menu_name'          => __('Guest Authors', 'ghostlabs-undercard'),
            ],

            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-groups',

            'show_in_menu' => 'users.php',

            'supports'     => ['title', 'editor', 'thumbnail', 'revisions', 'custom-fields'],
            'has_archive'  => false,
            'rewrite'      => [
                'slug'       => $slug,
                'with_front' => false,
            ],
        ]);

        $this->syncRewriteRules($slug);
    }

    public function syncRewriteRules(string $slug): void
    {
        if (get_option(self::REWRITE_BASE_OPTION) === $slug) {
            return;
        }

        delete_option('rewrite_rules');
        update_option(self::REWRITE_BASE_OPTION, $slug, true);
    }

    public function registerMeta(): void
    {
        $sanitizer = new ProfileFieldSanitizer();

        foreach (ProfileFields::forGuests() as $key => $field) {
            register_post_meta(self::POST_TYPE, $key, [
                'type'              => $field->restType(),
                'description'       => $field->description,
                'single'            => true,
                'show_in_rest'      => $field->isList()
                    ? ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]]
                    : true,
                'sanitize_callback' => static fn(mixed $value): string|int|array
                    => $sanitizer->sanitize($field, $value),

                'auth_callback'     => static fn(bool $allowed, string $meta, int $postId): bool
                    => current_user_can('edit_post', $postId),
            ]);
        }
    }

    public function bySlug(string $slug): ?Author
    {
        $slug = sanitize_title($slug);

        if ($slug === '') {
            return null;
        }

        $posts = get_posts([
            'name'             => $slug,
            'post_type'        => self::POST_TYPE,
            'post_status'      => 'publish',
            'numberposts'      => 1,
            'suppress_filters' => false,
        ]);

        $post = $posts[0] ?? null;

        return $post instanceof WP_Post ? self::toAuthor($post) : null;
    }

    public static function toAuthor(WP_Post $post): ?Author
    {
        $permalink = (string) get_permalink($post);

        if ($permalink === '') {
            return null;
        }

        $name = $post->post_title !== ''
            ? $post->post_title
            : (string) $post->post_name;

        try {
            return Author::guest(
                slug: (string) $post->post_name,
                name: $name,
                profileUrl: $permalink,

                bio: self::plainText($post->post_content),
                url: self::meta($post->ID, 'undercard_website'),

                firstName: self::nameParts($name)[0],
                lastName: self::nameParts($name)[1],
                jobTitle: self::meta($post->ID, 'undercard_job_title'),
                employer: self::meta($post->ID, 'undercard_employer'),
                employerUrl: self::meta($post->ID, 'undercard_employer_url'),
                education: self::meta($post->ID, 'undercard_education'),
                expertise: self::metaList($post->ID, 'undercard_expertise'),
                handle: self::meta($post->ID, 'undercard_handle'),
                avatarUrl: self::featuredImage($post->ID),
                phone: self::meta($post->ID, 'undercard_phone'),
                publicEmail: self::meta($post->ID, 'undercard_public_email'),
                street: self::meta($post->ID, 'undercard_street'),
                locality: self::meta($post->ID, 'undercard_locality'),
                country: self::meta($post->ID, 'undercard_country'),
                estYear: self::meta($post->ID, 'undercard_est_year'),
                statusLabel: self::meta($post->ID, 'undercard_status_label'),
                ctaLabel: self::meta($post->ID, 'undercard_cta_label'),
                ctaUrl: self::meta($post->ID, 'undercard_cta_url'),
                socialUrls: self::metaList($post->ID, 'undercard_social'),
            );
        } catch (InvalidArgumentException) {
            
            return null;
        }
    }

    private static function nameParts(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) < 2) {
            return ['', $parts[0] ?? ''];
        }

        $last = array_pop($parts);

        return [implode(' ', $parts), $last];
    }

    private static function featuredImage(int $postId): string
    {
        $url = get_the_post_thumbnail_url($postId, [Author::AVATAR_SIZE, Author::AVATAR_SIZE]);

        return is_string($url) ? $url : '';
    }

    private static function plainText(string $content): string
    {
        return PlainText::from(strip_shortcodes($content));
    }

    private static function meta(int $postId, string $key): string
    {
        $value = get_post_meta($postId, $key, true);

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function metaList(int $postId, string $key): array
    {
        $value = get_post_meta($postId, $key, true);

        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn(mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
            $value,
        )));
    }
}
