<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradePage
{
    
    public function __construct(
        private readonly UpgradePanel $panel,
        private readonly HeroBand $hero,
        private readonly ComparisonTable $table,
        private readonly string $tableUrl,
    ) {
    }

    public function render(): void
    {
        echo '<div class="ghostlabs-u-upgrade">';

        $this->intro();
        $this->panel->render();
        $this->hero->render();

        echo '<section class="ghostlabs-u-compare">';
        $this->table->render();
        $this->foot();
        echo '</section>';

        echo '</div>';
    }

    private function intro(): void
    {
        echo '<section class="ghostlabs-u-upgrade__card">';

        printf('<h2 class="ghostlabs-u-upgrade__card-title">%s</h2>', esc_html__('Undercard', 'ghostlabs-undercard'));

        printf(
            '<p>%s</p>',
            esc_html(sprintf(
                /* translators: %d: how many card designs the plugin has. */
                __('All %d card designs, guest authors, co-authors and credit roles are in this plugin and stay in it.', 'ghostlabs-undercard'),
                count(CardFamily::cases())
            ))
        );

        printf(
            '<p>%s</p>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s adds the half a machine reads — what it includes is below.', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ))
        );

        echo '</section>';
    }

    private function foot(): void
    {
        echo '<div class="ghostlabs-u-compare__foot"><div class="ghostlabs-u-compare__note">';

        printf('<p class="ghostlabs-u-compare__note-title">%s</p>', esc_html__('Ready when you are', 'ghostlabs-undercard'));
        printf('<p>%s</p>', esc_html__('Pricing and the full feature tour live on the product page.', 'ghostlabs-undercard'));

        echo '</div>';

        if ($this->tableUrl !== '') {
            printf(
                '<a class="ghostlabs-u-button ghostlabs-u-button--primary ghostlabs-u-button--large" href="%s" target="_blank" rel="noopener noreferrer">%s'
                . '<span class="screen-reader-text"> %s</span><span aria-hidden="true"> →</span></a>',
                esc_url($this->tableUrl),
                esc_html(sprintf(
                    /* translators: %s: the paid edition's name. */
                    __('See %s', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionName()
                )),
                esc_html__('(opens in a new tab)', 'ghostlabs-undercard')
            );
        }

        echo '</div>';
    }
}
