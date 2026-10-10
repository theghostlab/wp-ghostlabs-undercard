<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradePanel
{
    
    public function __construct(
        private readonly string $downloadUrl,
        private readonly ?string $uploadUrl,
    ) {
    }

    public function render(): void
    {
        $paid = TierMatrix::paidEditionName();

        echo '<section class="ghostlabs-u-upgrade__strip ghostlabs-u-inplace">';
        echo '<details class="ghostlabs-u-inplace__details">';

        printf(
            '<summary class="ghostlabs-u-inplace__summary"><span class="ghostlabs-u-inplace__heading">%s</span>'
            . '<span class="ghostlabs-u-button ghostlabs-u-button--secondary">%s</span></summary>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Already own %s?', 'ghostlabs-undercard'),
                $paid
            )),
            esc_html__('Have a license?', 'ghostlabs-undercard')
        );

        echo '<div class="ghostlabs-u-inplace__region">';

        printf(
            '<p class="ghostlabs-u-inplace__sub">%s</p>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s is a separate plugin. When you activate it, it switches this plugin off, and every setting, byline, guest and profile field stays exactly as it is.', 'ghostlabs-undercard'),
                $paid
            ))
        );

        echo '<ol class="ghostlabs-u-inplace__steps">';

        printf(
            '<li><a href="%s" target="_blank" rel="noopener noreferrer">%s<span class="screen-reader-text"> %s</span></a></li>',
            esc_url($this->downloadUrl),
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Download %s from your account', 'ghostlabs-undercard'),
                $paid
            )),
            esc_html__('(opens in a new tab)', 'ghostlabs-undercard')
        );

        if ($this->uploadUrl !== null) {
            printf(
                '<li><a href="%s">%s</a> %s</li>',
                esc_url($this->uploadUrl),
                esc_html__('Upload it to this site', 'ghostlabs-undercard'),
                esc_html__('under Plugins → Add New Plugin → Upload Plugin.', 'ghostlabs-undercard')
            );
        } else {
            printf(
                '<li>%s</li>',
                esc_html__('Upload it to this site under Plugins → Add New Plugin → Upload Plugin. Your account can\'t upload plugins here. Ask whoever manages plugins on this site to upload it for you.', 'ghostlabs-undercard')
            );
        }

        printf(
            '<li>%s</li>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Activate %s, then enter your license key in its settings.', 'ghostlabs-undercard'),
                $paid
            ))
        );

        echo '</ol></div></details></section>';
    }
}
