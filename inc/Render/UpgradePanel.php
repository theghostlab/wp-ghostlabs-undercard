<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradePanel
{
    
    public const REGION_ID = 'ghostlabs-undercard-inplace-form';

    public function __construct(
        private readonly bool $inPlace,
        private readonly string $refusal,
        private readonly ?string $claimToken,
    ) {
    }

    public function render(): void
    {
        if (! $this->inPlace) {
            $this->unavailable();

            return;
        }

        $returning = $this->claimToken !== null && $this->claimToken !== '';

        echo '<section class="ghostlabs-u-upgrade__strip ghostlabs-u-inplace" data-undercard-inplace>';
        echo '<div class="ghostlabs-u-inplace__row"><div class="ghostlabs-u-inplace__copy">';

        if ($returning) {
            printf('<h2 class="ghostlabs-u-inplace__heading">%s</h2>', esc_html__('Finish your upgrade', 'ghostlabs-undercard'));
            printf(
                '<p class="ghostlabs-u-inplace__sub">%s</p>',
                esc_html(sprintf(
                    /* translators: %s: the paid edition's short badge. */
                    __('Your purchase is complete. One click and this site switches to %s in place — no key entry needed, and every setting, byline, guest and profile field stays exactly as it is.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionBadge()
                ))
            );
        } else {
            $this->heading();
            printf(
                '<p class="ghostlabs-u-inplace__sub">%s</p>',
                esc_html(sprintf(
                    /* translators: %s: the paid edition's short badge. */
                    __('Paste your license key and this site switches to %s in place — every setting, byline, guest and profile field stays exactly as it is.', 'ghostlabs-undercard'),
                    TierMatrix::paidEditionBadge()
                ))
            );
        }

        echo '</div>';

        printf(
            '<button type="button" class="ghostlabs-u-button ghostlabs-u-button--secondary" aria-expanded="false" aria-controls="%s" data-undercard-inplace-toggle>%s</button>',
            esc_attr(self::REGION_ID),
            esc_html__('Have a license?', 'ghostlabs-undercard')
        );

        echo '</div>';

        if ($returning) {
            printf(
                '<div class="ghostlabs-u-inplace__claim"><button type="button" class="ghostlabs-u-button ghostlabs-u-button--primary" data-undercard-inplace-claim>%s</button></div>',
                esc_html__('Complete upgrade', 'ghostlabs-undercard')
            );
        }

        printf('<div id="%s" class="ghostlabs-u-inplace__region" hidden>', esc_attr(self::REGION_ID));
        echo '<form class="ghostlabs-u-inplace__form" data-undercard-inplace-form novalidate>';

        printf(
            '<label class="ghostlabs-u-inplace__field"><span class="ghostlabs-u-inplace__label">%s</span>'
            . '<input type="text" class="ghostlabs-u-inplace__input" name="key" autocomplete="off" spellcheck="false" data-undercard-inplace-key>'
            . '<span class="ghostlabs-u-inplace__notice" data-undercard-inplace-notice hidden></span></label>',
            esc_html__('License key', 'ghostlabs-undercard')
        );

        printf(
            '<button type="submit" class="ghostlabs-u-button ghostlabs-u-button--primary" data-undercard-inplace-submit>%s</button>',
            esc_html__('Verify and upgrade', 'ghostlabs-undercard')
        );

        echo '</form></div>';

        echo '<p role="status" class="ghostlabs-u-inplace__status" data-undercard-inplace-status></p>';

        echo '</section>';
    }

    private function unavailable(): void
    {
        echo '<section class="ghostlabs-u-upgrade__strip ghostlabs-u-inplace">';

        if ($this->claimToken !== null && $this->claimToken !== '') {

            printf('<h2 class="ghostlabs-u-inplace__heading">%s</h2>', esc_html__('Your purchase is complete', 'ghostlabs-undercard'));
        } else {
            $this->heading();
        }

        printf('<p class="ghostlabs-u-inplace__sub">%s</p>', esc_html($this->refusal));

        echo '</section>';
    }

    private function heading(): void
    {
        printf(
            '<h2 class="ghostlabs-u-inplace__heading">%s</h2>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Already own %s?', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ))
        );
    }
}
