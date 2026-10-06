<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

final class HeroBand
{
    
    public function __construct(private readonly string $url)
    {
    }

    public function render(): void
    {
        echo '<section class="ghostlabs-u-hero">';

        echo '<div class="ghostlabs-u-hero__texture" aria-hidden="true"></div>';
        echo '<div class="ghostlabs-u-hero__sweep" aria-hidden="true"></div>';

        echo '<div class="ghostlabs-u-hero__copy">';

        printf('<p class="ghostlabs-u-hero__eyebrow">%s</p>', esc_html(TierMatrix::paidEditionName()));

        printf(
            '<h2 class="ghostlabs-u-hero__headline">%s</h2>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's short badge. The register's lead line. */
                __('%s is everything a machine reads.', 'ghostlabs-undercard'),
                TierMatrix::paidEditionBadge()
            ))
        );

        printf(
            '<p class="ghostlabs-u-hero__sub">%s</p>',
            esc_html__('Every card, byline and guest author you already have — plus structured data, author profile pages and archives that follow the byline.', 'ghostlabs-undercard')
        );

        if ($this->url !== '') {

            printf(
                '<a class="ghostlabs-u-hero__pill" href="%s" target="_blank" rel="noopener noreferrer">%s'
                . '<span class="screen-reader-text"> %s</span><span aria-hidden="true"> →</span></a>',
                esc_url($this->url),
                esc_html(sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('See %s', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                )),
                esc_html__('(opens in a new tab)', 'ghostlabs-undercard')
            );
        }

        echo '</div></section>';
    }
}
