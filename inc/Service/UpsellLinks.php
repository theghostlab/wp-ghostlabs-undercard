<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpsellLinks
{
    public function register(): void
    {
        add_filter('ghostlabs_undercard_plugin_action_links', [ $this, 'actionLinks' ]);

        add_filter('ghostlabs_undercard_plugin_row_meta', [ $this, 'rowMeta' ]);
    }

    public function actionLinks(array $links): array
    {
        $links[] = $this->upgradeLink();

        return $links;
    }

    public function rowMeta(array $meta): array
    {
        $meta[] = $this->rowLink(
            UpgradeEndpoints::docsUrl(),
            __('Docs', 'ghostlabs-undercard'),
            __('Undercard docs', 'ghostlabs-undercard')
        );

        $meta[] = $this->rowLink(
            UpgradeEndpoints::supportUrl(),
            __('Support', 'ghostlabs-undercard'),
            __('Undercard support forum', 'ghostlabs-undercard')
        );

        return $meta;
    }

    private function upgradeLink(): string
    {

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight:700">%s'
                . '<span class="screen-reader-text"> %s</span></a>',
            esc_url(UpgradeEndpoints::upgradeUrl(UpsellSource::PluginsRow)),
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Upgrade to %s', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            )),

            esc_html__('(opens in a new tab)', 'ghostlabs-undercard')
        );
    }

    private function rowLink(string $url, string $label, string $accessibleName): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url($url),
            esc_attr($accessibleName),
            esc_html($label)
        );
    }
}
