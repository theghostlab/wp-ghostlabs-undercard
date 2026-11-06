<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;
use GHOSTLABS\UNDERCARD\Render\SchemaPreview;
use WP_Post;
use WP_User;

class SchemaPreviewPanel
{
    public function __construct(
        private readonly GuestAuthorsInterface $guests = new GuestAuthors(),
    ) {
    }

    public function register(): void
    {
        add_action('show_user_profile', [$this, 'renderForUser']);
        add_action('edit_user_profile', [$this, 'renderForUser']);
        add_action('add_meta_boxes', [$this, 'addGuestMetaBox']);
    }

    public function renderForUser(WP_User $user): void
    {
        if (! current_user_can('edit_user', $user->ID)) {
            return;
        }

        echo '<h2>' . esc_html__('Structured data preview', 'ghostlabs-undercard') . '</h2>';

        $this->render(Author::fromWpUser($user));
    }

    public function addGuestMetaBox(): void
    {
        add_meta_box(
            'undercard-schema-preview',
            __('Undercard — Structured data preview', 'ghostlabs-undercard'),
            [$this, 'renderForGuest'],
            GuestAuthors::POST_TYPE,
            'normal',
            'low'
        );
    }

    public function renderForGuest(WP_Post $post): void
    {
        $author = $this->guests->bySlug((string) $post->post_name);

        if ($author === null) {
            printf(
                '<p class="description">%s</p>',
                esc_html__('Save this guest to see what their structured data would say.', 'ghostlabs-undercard')
            );

            return;
        }

        $this->render($author);
    }

    private function upsell(): void
    {
        $url = Upsell::urlFor(UpsellSource::ProfilePreview);

        if ($url === '') {
            return;
        }

        printf(
            '<p class="description">%s</p>',
            wp_kses(
                sprintf(

                    /* translators: 1: the paid edition's name, in <strong>. 2: a comma-separated list of schema property names, each in <code>. */
                    __('%1$s, a separate plugin, publishes this profile as structured data, including %2$s.', 'ghostlabs-undercard'),
                    '<strong>' . esc_html(TierMatrix::paidEditionName()) . '</strong>',

                    implode(', ', array_map(
                        static fn (string $property): string => '<code>' . esc_html($property) . '</code>',
                        TierMatrix::CREDENTIAL_PROPERTIES
                    ))
                ),
                [ 'strong' => [], 'code' => [] ]
            )
        );

        printf(
            '<div class="ghostlabs-u-upsell-notice"><p class="ghostlabs-u-upsell-notice__claim">%s</p>'
                . '<p><a href="%s" target="_blank" rel="noopener noreferrer">%s'
                . '<span class="screen-reader-text"> %s</span><span aria-hidden="true"> ↗</span></a></p></div>',
            esc_html__('Your settings, bylines, guests and profile fields carry over unchanged.', 'ghostlabs-undercard'),
            esc_url($url),
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('See %s', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            )),
            esc_html__('(opens in a new tab)', 'ghostlabs-undercard')
        );
    }

    private function render(Author $author): void
    {
        $preview = new SchemaPreview($author);
        $rows    = $preview->rows();

        echo '<details class="ghostlabs-u-schema-preview">';

        printf(
            '<summary style="cursor:pointer;font-weight:600">%s</summary>',
            esc_html__('What this profile would publish', 'ghostlabs-undercard')
        );

        printf(
            '<p class="description">%s</p>',
            esc_html__(
                'A preview built from the fields above. Nothing on this screen is published — it shows what this profile would say, not what it says.',
                'ghostlabs-undercard'
            )
        );

        echo '<table class="widefat striped" style="max-width:48rem"><tbody>';

        foreach ($rows as $row) {
            printf(
                '<tr><th scope="row" style="width:11rem;text-align:start;font-weight:400"><code>%s</code></th><td>%s</td></tr>',
                esc_html($row['property']),
                esc_html($row['value'])
            );
        }

        echo '</tbody></table>';

        if ($preview->hasCredentials()) {
            $this->upsell();
        }

        echo '</details>';
    }
}
