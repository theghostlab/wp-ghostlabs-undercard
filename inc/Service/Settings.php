<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use Closure;
use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Config\SettingsCopy;
use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Dto\AssetMeta;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;
use GHOSTLABS\UNDERCARD\Render\ComparisonTable;
use GHOSTLABS\UNDERCARD\Render\HeroBand;
use GHOSTLABS\UNDERCARD\Render\UpgradePanel;
use GHOSTLABS\UNDERCARD\Render\UpgradePage;

class Settings
{
    
    public const TAB_SETTINGS = 'settings';

    public const TAB_UPGRADE = 'upgrade';

    private const STYLE_HANDLE = 'ghostlabs-undercard-admin';

    private const ASSET = 'build/admin/upgrade';

    private readonly Closure $canUpload;

    public function __construct(?Closure $canUpload = null)
    {
        $this->canUpload = $canUpload ?? static fn (): bool => current_user_can('upload_plugins');
    }

    public function page(): void
    {
        add_submenu_page(
            'options-general.php',
            __('Undercard Settings', 'ghostlabs-undercard'),
            __('Undercard', 'ghostlabs-undercard'),
            'manage_options',
            PluginConfig::$settingsMenuSlug,
            [$this, 'page_render'],
        );
    }

    public function scripts(): void
    {
        $screen = get_current_screen();

        if (!$screen || $screen->id !== 'settings_page_' . PluginConfig::$settingsMenuSlug) {
            return;
        }

        $meta = $this->assetMeta();

        wp_enqueue_style(
            self::STYLE_HANDLE,
            GHOSTLABS_UNDERCARD_PLUGIN_URL . 'build/admin/style-upgrade.css',
            [],
            $meta->version
        );

        if ($this->currentTab() === self::TAB_UPGRADE) {
            return;
        }

        wp_enqueue_script('wp-api-fetch');

        wp_add_inline_script(
            'wp-api-fetch',
            'window.ghostlabsUndercardSettings = ' . wp_json_encode([
                'path'     => sprintf('/%s/save-settings', PluginConfig::restNameSpace()),
                'messages' => SettingsCopy::messages(),
            ]) . ';' . "\n" . $this->submitScript(),
            'after'
        );
    }

    private function assetMeta(): AssetMeta
    {
        return AssetMeta::read(
            GHOSTLABS_UNDERCARD_PLUGIN_PATH . self::ASSET . '.asset.php',
            GHOSTLABS_UNDERCARD_VERSION
        );
    }

    public function page_render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $tab = $this->currentTab();

        echo '<div class="wrap ghostlabs-u-screen">';

        echo '<header class="ghostlabs-u-screen__header">';
        printf('<h1 class="ghostlabs-u-screen__title">%s</h1>', esc_html__('Undercard', 'ghostlabs-undercard'));
        printf('<span class="ghostlabs-u-screen__badge">%s</span>', esc_html__('Free', 'ghostlabs-undercard'));
        echo '</header>';
        echo '<hr class="wp-header-end">';

        $this->tabNav($tab);

        if ($tab === self::TAB_UPGRADE) {
            $this->upgradeTab();
        } else {
            $this->settingsTab();
        }

        echo '</div>';
    }

    private function currentTab(): string
    {
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only tab switch.
        $tab = isset($_GET['tab']) && is_string($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

        return $tab === self::TAB_UPGRADE ? self::TAB_UPGRADE : self::TAB_SETTINGS;
    }

    private function tabNav(string $current): void
    {
        $tabs = [
            self::TAB_SETTINGS => __('Settings', 'ghostlabs-undercard'),
            self::TAB_UPGRADE  => TierMatrix::paidEditionName(),
        ];

        printf(
            '<nav class="ghostlabs-u-tabs" aria-label="%s"><div class="ghostlabs-u-tabs__list">',
            esc_attr__('Undercard settings', 'ghostlabs-undercard')
        );

        foreach ($tabs as $slug => $label) {
            printf(
                '<a href="%s" class="ghostlabs-u-tabs__item%s"%s>%s</a>',
                esc_url(add_query_arg(
                    [ 'page' => PluginConfig::$settingsMenuSlug, 'tab' => $slug ],
                    admin_url('options-general.php')
                )),
                $slug === $current ? ' is-active' : '',
                $slug === $current ? ' aria-current="page"' : '',
                esc_html($label)
            );
        }

        echo '</div></nav>';
    }

    private function settingsTab(): void
    {
        $eligible = BylineField::eligiblePostTypes();
        $values   = (new SiteSettings())->values($eligible);
        $fields   = SettingsCopy::fields();
        $messages = SettingsCopy::messages();

        echo '<div class="ghostlabs-u-settings">';
        echo '<form id="ghostlabs-undercard-settings" class="ghostlabs-u-settings__form">';
        echo '<div class="ghostlabs-u-settings__panel">';

        $this->postTypesRow($fields['bylinePostTypes'], SettingsCopy::postTypes($eligible), $values['bylinePostTypes']);
        $this->textRow('guestSlug', $fields['guestSlug'], $values['guestSlug']);
        $this->checkboxRow('bylineFollowsCore', $fields['bylineFollowsCore'], $values['bylineFollowsCore']);
        $this->checkboxRow('vcardEnabled', $fields['vcardEnabled'], $values['vcardEnabled']);

        echo '</div>';

        echo '<div class="ghostlabs-u-settings__actions">';
        printf(
            '<button type="submit" class="button button-primary button-large">%s</button>',
            esc_html($messages['save'])
        );
        echo '<div id="ghostlabs-undercard-notice" class="ghostlabs-u-settings__status" role="status" aria-live="polite" aria-atomic="true"></div>';
        echo '</div>';
        echo '<div id="ghostlabs-undercard-error" class="ghostlabs-u-settings__error" role="alert"></div>';

        echo '</form></div>';
    }

    private function upgradeTab(): void
    {
        (new UpgradePage(
            new UpgradePanel(
                AccountEndpoints::accountUrl(),
                ($this->canUpload)() ? admin_url('plugin-install.php?tab=upload') : null
            ),
            new HeroBand(UpgradeEndpoints::upgradeUrl(UpsellSource::Settings)),
            new ComparisonTable(),
            UpgradeEndpoints::upgradeUrl(UpsellSource::SettingsTable)
        ))->render();
    }

    private function postTypesRow(array $field, array $types, array $selected): void
    {
        echo '<div class="ghostlabs-u-settings__field">';
        printf(
            '<div class="ghostlabs-u-settings__title" id="ghostlabs-undercard-post-types-title">%s</div>',
            esc_html($field['label'])
        );
        echo '<div class="ghostlabs-u-settings__content" role="group" aria-labelledby="ghostlabs-undercard-post-types-title" aria-describedby="ghostlabs-undercard-post-types-help">';

        foreach ($types as $type) {
            printf(
                '<label class="ghostlabs-u-settings__check"><input type="checkbox" name="bylinePostTypes[]" value="%s"%s> %s</label>',
                esc_attr($type['name']),
                in_array($type['name'], $selected, true) ? ' checked' : '',
                esc_html($type['label'])
            );
        }

        $this->help('ghostlabs-undercard-post-types-help', $field);
        echo '</div></div>';
    }

    private function textRow(string $name, array $field, string $value): void
    {
        echo '<div class="ghostlabs-u-settings__field">';
        printf(
            '<label class="ghostlabs-u-settings__title" for="%1$s">%2$s</label>'
            . '<div class="ghostlabs-u-settings__content">'
            . '<input name="%1$s" id="%1$s" type="text" maxlength="64" value="%3$s" class="ghostlabs-u-settings__input" aria-describedby="%1$s-help">',
            esc_attr($name),
            esc_html($field['label']),
            esc_attr($value)
        );
        $this->help($name . '-help', $field);
        echo '</div></div>';
    }

    private function checkboxRow(string $name, array $field, bool $checked): void
    {
        echo '<div class="ghostlabs-u-settings__field">';
        printf(
            '<div class="ghostlabs-u-settings__title" aria-hidden="true">%2$s</div>'
            . '<div class="ghostlabs-u-settings__content">'
            . '<label class="ghostlabs-u-settings__check"><input name="%1$s" id="%1$s" type="checkbox" value="1"%3$s aria-describedby="%1$s-help"> %2$s</label>',
            esc_attr($name),
            esc_html($field['label']),
            $checked ? ' checked' : ''
        );
        $this->help($name . '-help', $field);
        echo '</div></div>';
    }

    private function help(string $id, array $field): void
    {
        $text = esc_html($field['description']);

        if ($field['code'] !== '') {
            $text = str_replace('%s', '<code>' . esc_html($field['code']) . '</code>', $text);
        }

        printf(
            '<p class="ghostlabs-u-settings__help" id="%s">%s</p>',
            esc_attr($id),
            $text // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above; the only markup is the <code> composed here.
        );
    }

    private function submitScript(): string
    {

        return implode("\n", [
            '( function ( config ) {',
            '  document.addEventListener( \'DOMContentLoaded\', function () {',
            '    var form = document.getElementById( \'ghostlabs-undercard-settings\' );',
            '    var status = document.getElementById( \'ghostlabs-undercard-notice\' );',
            '    var alertBox = document.getElementById( \'ghostlabs-undercard-error\' );',
            '    if ( ! form || ! config ) { return; }',
            '    var button = form.querySelector( \'button[type="submit"]\' );',
            '    var busy = false;',
            '',
            '    // Busy is aria-disabled, never disabled: a disabled button drops',
            '    // keyboard focus to the document, and the guard does the blocking.',
            '    function setBusy( on ) {',
            '      busy = on;',
            '      button.setAttribute( \'aria-disabled\', on ? \'true\' : \'false\' );',
            '      button.classList.toggle( \'disabled\', on );',
            '    }',
            '',
            '    form.addEventListener( \'submit\', function ( event ) {',
            '      event.preventDefault();',
            '      if ( busy ) { return; }',
            '',
            '      var data = new FormData( form );',
            '      var payload = {',
            '        type: \'settings\',',
            '        bylinePostTypes: data.getAll( \'bylinePostTypes[]\' ).join( \',\' ),',
            '        guestSlug: data.get( \'guestSlug\' ) || \'\',',
            '        bylineFollowsCore: form.bylineFollowsCore.checked ? \'1\' : \'\',',
            '        vcardEnabled: form.vcardEnabled.checked ? \'1\' : \'\'',
            '      };',
            '',
            '      status.textContent = config.messages.saving;',
            '      status.removeAttribute( \'data-type\' );',
            '      alertBox.textContent = \'\';',
            '      form.guestSlug.removeAttribute( \'aria-invalid\' );',
            '      form.guestSlug.setAttribute( \'aria-describedby\', \'guestSlug-help\' );',
            '      setBusy( true );',
            '',
            '      wp.apiFetch( { path: config.path, method: \'POST\', data: payload } )',
            '        .then( function ( response ) {',
            '          // The handler slugifies the base, so show what was stored.',
            '          var stored = response && response.success && response.success.guestSlug;',
            '          if ( stored && stored.value ) { form.guestSlug.value = stored.value; }',
            '          status.textContent = config.messages.saved;',
            '          status.setAttribute( \'data-type\', \'success\' );',
            '        } )',
            '        .catch( function ( error ) {',
            '          // A guest base somebody else owns comes back as a 412 naming the field',
            '          // (F3): say that, and mark the field, not "could not be saved".',
            '          var errors = error && error.data && error.data.errors;',
            '          var message = errors && errors.guestSlug && errors.guestSlug[ 0 ];',
            '          status.textContent = \'\';',
            '          alertBox.textContent = message || config.messages.failed;',
            '          if ( message ) {',
            '            form.guestSlug.setAttribute( \'aria-invalid\', \'true\' );',
            '            form.guestSlug.setAttribute( \'aria-describedby\', \'ghostlabs-undercard-error guestSlug-help\' );',
            '          }',
            '        } )',
            '        .finally( function () {',
            '          setBusy( false );',
            '        } );',
            '    } );',
            '  } );',
            '}( window.ghostlabsUndercardSettings ) );',
        ]);
    }
}
