<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Config;

final class SettingsCopy
{
    
    public static function fields(): array
    {
        return [
            'bylinePostTypes'   => [
                'label'       => __('Post types with a byline', 'ghostlabs-undercard'),
                'description' => __('Only post types that support authors are listed — a byline needs somewhere to attach.', 'ghostlabs-undercard'),
                'code'        => '',
            ],
            'guestSlug'         => [
                'label'       => __('Guest permalink base', 'ghostlabs-undercard'),

                /* translators: %s: the word "author", shown as code. */
                'description' => __('Guest author pages live under this path. Not %s — core owns that, and a guest whose slug matched a user would shadow them.', 'ghostlabs-undercard'),
                'code'        => 'author',
            ],
            'bylineFollowsCore' => [
                'label'       => __("Core's byline follows Undercard", 'ghostlabs-undercard'),
                'description' => __('Keeps the theme byline and the card naming the same people. With this off a post can say two different things about who wrote it.', 'ghostlabs-undercard'),
                'code'        => '',
            ],
            'vcardEnabled'      => [
                'label'       => __('Enable the contact download', 'ghostlabs-undercard'),
                'description' => __('A site-wide switch. Each author still decides what — if anything — their card publishes.', 'ghostlabs-undercard'),
                'code'        => '',
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'save'   => __('Save Changes', 'ghostlabs-undercard'),
            'saving' => __('Saving…', 'ghostlabs-undercard'),
            'saved'  => __('Settings saved.', 'ghostlabs-undercard'),
            'failed' => __('The settings could not be saved. Reload the page and try again.', 'ghostlabs-undercard'),
        ];
    }

    public static function guestBaseTaken(string $slug, string $owner): string
    {
        return sprintf(
            /* translators: 1: the permalink base the admin typed, 2: what already uses it, e.g. "the page “About”". */
            __('“%1$s” is already used by %2$s, so guest author pages there would hide it. Choose a different base.', 'ghostlabs-undercard'),
            $slug,
            $owner
        );
    }

    public static function guestBaseOwnerCore(): string
    {
        return __('WordPress’s own addresses, such as author archives and search', 'ghostlabs-undercard');
    }

    public static function guestBaseOwnerPostType(string $label): string
    {
        /* translators: %s: a post type's name, e.g. "Products". */
        return sprintf(__('the post type “%s”', 'ghostlabs-undercard'), $label);
    }

    public static function guestBaseOwnerTaxonomy(string $label): string
    {
        /* translators: %s: a taxonomy's name, e.g. "Genres". */
        return sprintf(__('the taxonomy “%s”', 'ghostlabs-undercard'), $label);
    }

    public static function guestBaseOwnerPage(string $title): string
    {
        /* translators: %s: a page's title. */
        return sprintf(__('the page “%s”', 'ghostlabs-undercard'), $title);
    }

    public static function postTypes(array $names): array
    {
        $types = [];

        foreach ($names as $name) {
            $object = get_post_type_object($name);

            $types[] = [
                'name'  => $name,
                'label' => $object !== null ? (string) $object->labels->name : $name,
            ];
        }

        return $types;
    }
}
