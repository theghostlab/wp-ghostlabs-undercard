<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\ProfileField;
use GHOSTLABS\UNDERCARD\Config\ProfileFields;
use GHOSTLABS\UNDERCARD\Enum\FieldType;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;

final class UserProfile
{
    private const NONCE_ACTION = 'undercard_profile_save';
    private const NONCE_NAME   = 'undercard_profile_nonce';

    public function __construct(
        private readonly ProfileFieldSanitizer $sanitizer = new ProfileFieldSanitizer(),
    ) {
    }

    public function register(): void
    {
        add_action('init', [$this, 'registerMeta']);
        add_filter('user_contactmethods', [$this, 'contactMethods']);
        add_action('show_user_profile', [$this, 'render']);
        add_action('edit_user_profile', [$this, 'render']);
        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueMedia']);
        add_filter('rest_prepare_user', [$this, 'hideFieldsFromPublicReads'], 10, 3);
    }

    public function registerMeta(): void
    {
        foreach (ProfileFields::forUsers() as $key => $field) {
            register_meta('user', $key, [
                'type'              => $field->restType(),
                'description'       => $field->description,
                'single'            => true,
                'show_in_rest'      => $field->isList()
                    ? ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]]
                    : true,
                'sanitize_callback' => fn(mixed $value): string|int|array => $this->sanitizer->sanitize($field, $value),

                'auth_callback'     => static fn(bool $allowed, string $meta, int $userId): bool
                    => current_user_can('edit_user', $userId),
            ]);
        }
    }

    public function hideFieldsFromPublicReads(
        WP_REST_Response $response,
        WP_User $user,
        WP_REST_Request $request,
    ): WP_REST_Response {

        if ($request->get_param('context') === 'edit') {
            return $response;
        }

        $data = $response->get_data();

        if (! is_array($data) || ! isset($data['meta']) || ! is_array($data['meta'])) {
            return $response;
        }

        foreach (array_keys(ProfileFields::forUsers()) as $key) {
            unset($data['meta'][$key]);
        }

        $response->set_data($data);

        return $response;
    }

    public function contactMethods(array $methods): array
    {
        foreach (ProfileFields::contactMethods() as $key => $field) {
            $methods[$key] = $field->label;
        }

        return $methods;
    }

    public function render(WP_User $user): void
    {
        $fields = ProfileFields::ownSection();

        echo '<h2>' . esc_html__('Undercard — Author Identity', 'ghostlabs-undercard') . '</h2>';
        echo '<table class="form-table" role="presentation"><tbody>';

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        foreach ($fields as $key => $field) {
            $value = get_user_meta($user->ID, $key, true);

            $labeled = $field->type !== FieldType::Attachment;

            echo '<tr><th>';
            echo $labeled
                ? '<label for="' . esc_attr($key) . '">' . esc_html($field->label) . '</label>'
                : '<span aria-hidden="true">' . esc_html($field->label) . '</span>';
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

    public function save(int $userId): void
    {
        if (! current_user_can('edit_user', $userId)) {
            return;
        }

        $nonce = isset($_POST[self::NONCE_NAME]) && is_string($_POST[self::NONCE_NAME])
            ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        foreach (ProfileFields::forUsers() as $key => $field) {

            if (! array_key_exists($key, $_POST)) {
                continue;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized on the next line by ProfileFieldSanitizer, per field type.
            $raw   = wp_unslash($_POST[$key]);
            $value = $this->sanitizer->sanitize($field, $raw);

            if ($value === '' || $value === [] || $value === 0) {
                delete_user_meta($userId, $key);
                continue;
            }

            update_user_meta($userId, $key, $value);
        }
    }

    public function enqueueMedia(string $hook): void
    {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }

        wp_enqueue_media();

        wp_register_script(
            'undercard-profile-media',
            false,
            ['media-editor'],
            GHOSTLABS_UNDERCARD_VERSION,
            true,
        );
        wp_enqueue_script('undercard-profile-media');
        wp_add_inline_script('undercard-profile-media', $this->mediaScript());
    }

    private function mediaScript(): string
    {
        $title  = esc_js(__('Choose a profile photo', 'ghostlabs-undercard'));
        $select = esc_js(__('Use this photo', 'ghostlabs-undercard'));

        return implode("\n", [
            'document.addEventListener( \'click\', function ( event ) {',
            '    var button = event.target.closest( \'[data-uc-media]\' );',
            '',
            '    if ( ! button ) {',
            '        return;',
            '    }',
            '',
            '    event.preventDefault();',
            '',
            '    var key     = button.getAttribute( \'data-uc-media\' );',
            '    var field   = document.getElementById( key );',
            '    var preview = document.querySelector( \'[data-uc-media-preview="\' + key + \'"]\' );',
            '',
            '    if ( ! field ) {',
            '        return;',
            '    }',
            '',
            '    if ( button.getAttribute( \'data-uc-media-action\' ) === \'remove\' ) {',
            '        field.value = \'\';',
            '',
            '        if ( preview ) {',
            '            preview.replaceChildren();',
            '        }',
            '',
            '        return;',
            '    }',
            '',
            '    var frame = wp.media( {',
            "        title: '" . $title . "',",
            "        button: { text: '" . $select . "' },",
            '        library: { type: \'image\' },',
            '        multiple: false',
            '    } );',
            '',
            '    frame.on( \'select\', function () {',
            '        var image = frame.state().get( \'selection\' ).first().toJSON();',
            '',
            '        field.value = image.id;',
            '',
            '        if ( preview ) {',
            '            var src = image.sizes && image.sizes.thumbnail',
            '                ? image.sizes.thumbnail.url',
            '                : image.url;',
            '',
            '            // Built as a node rather than concatenated into innerHTML.',
            '            // WordPress\' own sanitize_file_name() strips the quote that',
            '            // would be needed to break out of the src attribute, and',
            '            // this screen is edit_user-gated either way — but "safe',
            '            // because of what something else strips" is a property that',
            '            // stops holding the moment either half changes, and setting',
            '            // .src is not markup at all.',
            '            var img = document.createElement( \'img\' );',
            '',
            '            img.src    = src;',
            '            img.alt    = \'\';',
            '            img.width  = 96;',
            '            img.height = 96;',
            '',
            '            preview.replaceChildren( img );',
            '        }',
            '',
            '    } );',
            '',
            '    frame.open();',
            '} );',
            '',
        ]);
    }

    private function control(ProfileField $field, mixed $value): string
    {
        if ($field->type === FieldType::Attachment) {
            return $this->attachmentControl($field, $value);
        }

        $describedBy = $field->description === ''
            ? ''
            : ' aria-describedby="' . esc_attr($field->key) . '-description"';

        if ($field->type === FieldType::Boolean) {
            return $this->switchControl($field, $value, $describedBy);
        }

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

    private function switchControl(ProfileField $field, mixed $value, string $describedBy): string
    {
        $key = esc_attr($field->key);
        $on  = is_scalar($value) && (string) $value === '1';

        return '<input type="hidden" name="' . $key . '" value="" />'
            . '<input type="checkbox" name="' . $key . '" id="' . $key . '" value="1"'
            . ($on ? ' checked="checked"' : '')
            . $describedBy
            . ' />';
    }

    private function attachmentControl(ProfileField $field, mixed $value): string
    {
        $id  = is_scalar($value) ? absint($value) : 0;
        $key = esc_attr($field->key);

        $preview = $id > 0 ? wp_get_attachment_image($id, [96, 96], false, ['alt' => '']) : '';

        $describedBy = $key . '-legend' . ($field->description === '' ? '' : ' ' . $key . '-description');

        return '<fieldset class="ghostlabs-u-media-field">'
            . '<legend id="' . $key . '-legend" class="ghostlabs-u-media-legend">'
            . esc_html($field->label)
            . '</legend>'
            . '<div class="ghostlabs-u-media" data-uc-media-preview="' . $key . '">'
            . $preview
            . '</div>'
            . '<input type="hidden" name="' . $key . '" id="' . $key . '" value="'
            . esc_attr((string) $id) . '" />'
            . '<button type="button" class="button" data-uc-media="' . $key . '"'
            . ' aria-describedby="' . $describedBy . '">'
            . esc_html__('Choose photo', 'ghostlabs-undercard')
            . '</button> '
            . '<button type="button" class="button-link" data-uc-media="' . $key . '"'
            . ' data-uc-media-action="remove" aria-describedby="' . $describedBy . '">'
            . esc_html__('Remove', 'ghostlabs-undercard')
            . '</button>'
            . '</fieldset>';
    }

}
