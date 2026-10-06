<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

final class PluginMeta
{
    
    public function actionLinks(array $links): array
    {
        
        $needsAttention = (bool) apply_filters('ghostlabs_undercard_settings_link_needs_attention', false);

        array_unshift($links, $this->settingsLink($needsAttention));

        return apply_filters('ghostlabs_undercard_plugin_action_links', $links);
    }

    public function rowMeta(array $meta, string $pluginFile): array
    {
        if ($pluginFile !== self::basename()) {
            return $meta;
        }

        return apply_filters('ghostlabs_undercard_plugin_row_meta', $meta);
    }

    public function settingsLink(bool $needsAttention = false): string
    {
        $url   = admin_url('options-general.php?page=' . PluginConfig::$settingsMenuSlug);
        $label = __('Settings', 'ghostlabs-undercard');
        $style = $needsAttention ? ' style="color: #3858e9; font-weight: 700;"' : '';

        return sprintf(
            '<a href="%s" aria-label="%s"%s>%s</a>',
            esc_url($url),
            esc_attr($label),
            $style,
            esc_html($label)
        );
    }

    public static function basename(): string
    {
        return defined('GHOSTLABS_UNDERCARD_PLUGIN_SLUG')
            ? (string) constant('GHOSTLABS_UNDERCARD_PLUGIN_SLUG')
            : '';
    }
}
