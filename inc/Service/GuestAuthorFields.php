<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\ProfileField;
use GHOSTLABS\UNDERCARD\Enum\FieldType;
use GHOSTLABS\UNDERCARD\Config\ProfileFields;
use WP_Post;

final class GuestAuthorFields
{
    private const NONCE_ACTION = 'undercard_guest_save';
    private const NONCE_NAME   = 'undercard_guest_nonce';

    public function __construct(
        private readonly ProfileFieldSanitizer $sanitizer = new ProfileFieldSanitizer(),
    ) {
    }

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post_' . GuestAuthors::POST_TYPE, [$this, 'save'], 10, 2);
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'undercard-guest-identity',
            __('Undercard — Author Identity', 'ghostlabs-undercard'),
            [$this, 'render'],
            GuestAuthors::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function fields(): array
    {
        return ProfileFields::forGuests();
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        echo '<p class="description">'
            . esc_html__(
                'The editor above is this author’s bio, and the featured image is their portrait.',
                'ghostlabs-undercard'
            )
            . '</p>';

        echo '<table class="form-table" role="presentation"><tbody>';

        foreach (self::fields() as $key => $field) {
            $value = get_post_meta($post->ID, $key, true);

            $labeled = $field->type !== FieldType::Attachment;

            echo '<tr><th>';
            echo $labeled
                ? '<label for="' . esc_attr($key) . '">' . esc_html($field->label) . '</label>'
                : esc_html($field->label);
            echo '</th><td>';

            echo $this->control($field, $value); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if ($field->description !== '') {
                echo '<p class="description" id="' . esc_attr($key) . '-description">'
                    . esc_html($field->description) . '</p>';
            }

            echo '</td></tr>';
        }

        echo '</tbody></table>';
    }

    public function save(int $postId, WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $nonce = isset($_POST[self::NONCE_NAME]) && is_string($_POST[self::NONCE_NAME])
            ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        foreach (self::fields() as $key => $field) {

            if (! array_key_exists($key, $_POST)) {
                continue;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized on the next line by ProfileFieldSanitizer, per field type.
            $raw   = wp_unslash($_POST[$key]);
            $value = $this->sanitizer->sanitize($field, $raw);

            if ($value === '' || $value === [] || $value === 0) {
                delete_post_meta($postId, $key);
                continue;
            }

            update_post_meta($postId, $key, $value);
        }
    }

    private function control(ProfileField $field, mixed $value): string
    {
        if ($field->type === FieldType::Attachment) {

            return '<p class="description">'
                . esc_html__('Set the featured image to use as the portrait.', 'ghostlabs-undercard')
                . '</p>';
        }

        $describedBy = $field->description === ''
            ? ''
            : ' aria-describedby="' . esc_attr($field->key) . '-description"';

        if ($field->isList()) {
            $lines = is_array($value) ? implode("\n", array_map('strval', $value)) : (string) $value;

            return '<textarea name="' . esc_attr($field->key) . '" id="' . esc_attr($field->key)
                . '" rows="4" class="regular-text"' . $describedBy . '>'
                . esc_textarea($lines) . '</textarea>';
        }

        return '<input type="' . esc_attr($field->type->inputType()) . '"'
            . ' name="' . esc_attr($field->key) . '"'
            . ' id="' . esc_attr($field->key) . '"'
            . ' value="' . esc_attr(is_scalar($value) ? (string) $value : '') . '"'
            . $describedBy
            . ' class="regular-text" />';
    }
}
