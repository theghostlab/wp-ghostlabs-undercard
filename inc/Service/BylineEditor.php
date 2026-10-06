<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Enum\AuthorRole;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;

final class BylineEditor
{
    private const HANDLE = 'ghostlabs-undercard-byline';

    private const ASSET = 'build/editor/byline';

    public function __construct(private readonly BylineField $field)
    {
    }

    public function register(): void
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        $base = GHOSTLABS_UNDERCARD_PLUGIN_PATH . self::ASSET;

        if (! is_readable($base . '.asset.php')) {
            return;
        }

        $meta = require $base . '.asset.php';

        wp_enqueue_script(
            self::HANDLE,
            GHOSTLABS_UNDERCARD_PLUGIN_URL . self::ASSET . '.js',
            $meta['dependencies'],
            $meta['version'],
            true,
        );

        wp_enqueue_style(
            self::HANDLE,
            GHOSTLABS_UNDERCARD_PLUGIN_URL . self::ASSET . '.css',
            [],
            $meta['version'],
        );

        wp_set_script_translations(self::HANDLE, 'ghostlabs-undercard');

        wp_add_inline_script(
            self::HANDLE,
            'window.GHOSTLABS_UNDERCARD_BYLINE = ' . wp_json_encode($this->config()) . ';',
            'before',
        );
    }

    private function config(): array
    {
        return [
            'field'     => BylineField::FIELD,

            'postTypes' => $this->field->postTypes(),

            'roles' => array_map(
                static fn(AuthorRole $role): array => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ],
                AuthorRole::cases(),
            ),
            'restNamespace' => PluginConfig::restNameSpace(),
            'upsell'        => $this->upsell(),
        ];
    }

    private function upsell(): ?array
    {
        $url     = Upsell::urlFor(UpsellSource::BylineSecondCredit);
        $metaKey = Upsell::dismissalKeyFor(UpsellSource::BylineSecondCredit);

        if ($url === '' || $metaKey === '') {
            return null;
        }

        return [
            'url'         => $url,
            'metaKey'     => $metaKey,
            'editionName' => TierMatrix::paidEditionName(),
            'dismissed'   => Upsell::isDismissed(UpsellSource::BylineSecondCredit),
        ];
    }
}
