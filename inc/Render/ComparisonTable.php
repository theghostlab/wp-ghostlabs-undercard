<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

final class ComparisonTable
{
    
    private const CHECK = 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z';

    private const CROSS = 'M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z';

    public function render(): void
    {
        echo '<table class="ghostlabs-u-compare__table">';

        printf(
            '<caption class="screen-reader-text">%s</caption>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('What the free edition includes, and what %s adds', 'ghostlabs-undercard'),
                TierMatrix::paidEditionName()
            ))
        );

        printf(
            '<thead><tr><th scope="col" class="ghostlabs-u-compare__feature"><span class="screen-reader-text">%s</span></th>'
            . '<th scope="col" class="ghostlabs-u-compare__free">%s</th>'
            . '<th scope="col" class="ghostlabs-u-compare__paid">%s</th></tr></thead>',
            esc_html__('Feature', 'ghostlabs-undercard'),
            esc_html__('Free', 'ghostlabs-undercard'),
            esc_html(TierMatrix::paidEditionBadge())
        );

        echo '<tbody>';

        foreach (TierMatrix::capabilities() as $capability) {
            $this->row($capability);
        }

        echo '</tbody></table>';
    }

    private function row(array $capability): void
    {
        $shared = is_string($capability['free']) && $capability['free'] === $capability['paid'];

        printf('<tr data-capability="%s"><th scope="row">%s', esc_attr($capability['key']), esc_html($capability['label']));

        if ($shared) {
            printf('<span class="ghostlabs-u-compare__quantity">%s</span>', esc_html((string) $capability['free']));
        }

        echo '</th>';

        echo '<td class="ghostlabs-u-compare__free">';
        $this->cell($shared ? true : $capability['free'], true);
        echo '</td><td class="ghostlabs-u-compare__paid">';
        $this->cell($shared ? true : $capability['paid'], false);
        echo '</td></tr>';
    }

    private function cell(bool|string $value, bool $muted): void
    {
        if (is_string($value)) {
            echo esc_html($value);

            return;
        }

        $this->mark(
            $value ? self::CHECK : self::CROSS,
            $value ? $muted : true,
            $value ? __('Included', 'ghostlabs-undercard') : __('Not included', 'ghostlabs-undercard')
        );
    }

    private function mark(string $path, bool $muted, string $word): void
    {
        printf(
            '<svg class="ghostlabs-u-compare__mark%s" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false">'
            . '<path fill-rule="evenodd" clip-rule="evenodd" d="%s"/></svg>'
            . '<span class="screen-reader-text">%s</span>',
            $muted ? ' ghostlabs-u-compare__mark--muted' : '',
            esc_attr($path),
            esc_html($word)
        );
    }
}
