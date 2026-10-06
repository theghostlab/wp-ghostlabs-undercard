<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;
use GHOSTLABS\UNDERCARD\Service\AuthorStats;

final class AuroraCard
{
    use CollapsesToChip;

    private const FULL = [CardLayout::Vertical, CardLayout::Horizontal];

    public function __construct(
        private readonly AvatarResolver $avatars = new AvatarResolver(),
        private readonly AuthorStats $stats = new AuthorStats(),
    ) {
    }

    public function render(Author $author, array $attributes = []): string
    {
        $layout = CardLayout::fromStored(self::attr($attributes, 'layout', 'vertical'));
        $scheme = self::attr($attributes, 'scheme', 'dark');

        $html = sprintf(
            '<div class="ghostlabs-u-card ghostlabs-u-card--aurora" data-uc-family="aurora"'
            . ' data-uc-scheme="%s" data-uc-layout="%s" style="--ghostlabs-u-ratio:%s;%s">',
            esc_attr($scheme),
            esc_attr($layout->value),
            esc_attr(Geometry::ratio(CardFamily::Aurora, $layout, 0)),
            self::collapsedRatioProperty(CardFamily::Aurora, $layout),
        );

        $html .= '<div class="ghostlabs-u-card__inner">';
        $html .= $this->identity($author, $layout, self::attr($attributes, 'avatar', 'photo'), HeadingLevel::tag($attributes));
        $html .= $this->detail($author, $layout);

        return $html . '</div></div>';
    }

    private function identity(Author $author, CardLayout $layout, string $variant, string $nameTag): string
    {
        $html = '<div class="ghostlabs-u-card__identity">'
            . $this->avatar($author, $variant);

        if ($layout !== CardLayout::Mini && $layout !== CardLayout::Row) {
            $html .= $this->status($author);
        }

        $html .= '<div class="ghostlabs-u-card__names">'
            . $this->slot('name', $author->name(), $nameTag)
            . $this->slot('handle', $author->handle())
            . '</div>';

        return $html . '</div>';
    }

    private function detail(Author $author, CardLayout $layout): string
    {
        if ($layout === CardLayout::Chip) {
            return '';
        }

        $html = '<div class="ghostlabs-u-card__detail">';

        if (in_array($layout, self::FULL, true)) {
            $html .= $this->slot('role', $this->role($author));
        }

        $html .= $this->statStrip($author, $layout);

        if ($layout === CardLayout::Row) {
            return $html . $this->actions($author, $layout) . '</div>';
        }

        if ($layout === CardLayout::Mini) {
            return $html . '</div>';
        }

        return $html . $this->actions($author, $layout) . $this->link($author) . '</div>';
    }

    private function avatar(Author $author, string $variant): string
    {
        $avatar = $variant === 'monogram'
            ? new ResolvedAvatar(AvatarSource::Initials, $author->initials())
            : $this->avatars->resolve($author);

        $html = sprintf(
            '<figure class="ghostlabs-u-card__avatar" data-uc-avatar="%s">',
            esc_attr(strtolower($avatar->source->name)),
        );

        if ($avatar->isImage()) {

            $html .= sprintf(
                '<img class="ghostlabs-u-card__avatar-image" src="%s" alt=""'
                . ' width="192" height="192" decoding="async" />',
                esc_url($avatar->value),
            );
        } elseif ($avatar->value !== '') {

            $html .= '<figcaption class="ghostlabs-u-card__initials" aria-hidden="true">'
                . esc_html($avatar->value) . '</figcaption>';
        } else {
            $html .= '<span class="ghostlabs-u-card__initials ghostlabs-u-card__initials--icon"'
                . ' aria-hidden="true">' . Icons::person() . '</span>';
        }

        return $html . '</figure>';
    }

    private function status(Author $author): string
    {
        $label = $author->statusLabel();

        if ($label === '') {
            return '';
        }

        return '<p class="ghostlabs-u-card__status">'
            . '<span class="ghostlabs-u-card__status-dot" aria-hidden="true"></span>'
            . esc_html($label)
            . '</p>';
    }

    private function role(Author $author): string
    {
        $parts = array_filter([$author->jobTitle(), $author->primaryExpertise()]);

        return implode(' — ', $parts);
    }

    private function statStrip(Author $author, CardLayout $layout): string
    {
        $stats = $this->stats->for($author);

        if ($stats === []) {
            return '';
        }

        if ($layout === CardLayout::Row) {
            $stats = array_slice($stats, 0, 1);
        }

        $html = '<dl class="ghostlabs-u-card__stats">';

        foreach ($stats as $stat) {

            $html .= '<div class="ghostlabs-u-card__stat">'
                . '<dd class="ghostlabs-u-card__stat-value">' . esc_html($stat->value) . '</dd>'
                . '<dt class="ghostlabs-u-card__stat-label">' . esc_html($stat->label) . '</dt>'
                . '</div>';
        }

        return $html . '</dl>';
    }

    private function actions(Author $author, CardLayout $layout): string
    {
        $custom     = $author->ctaUrl() !== '';
        $primaryUrl = $custom ? $author->ctaUrl() : $author->profileUrl();
        $primary    = esc_url($primaryUrl);

        if ($primary === '') {
            return '';
        }

        $label = $author->ctaLabel() !== ''
            ? $author->ctaLabel()
            : ($custom
                ? __('Learn more', 'ghostlabs-undercard')
                : __('View posts', 'ghostlabs-undercard'));

        $html = '<p class="ghostlabs-u-card__actions">'
            . sprintf(
                '<a class="ghostlabs-u-card__cta ghostlabs-u-card__cta--primary" href="%s">%s</a>',
                $primary,
                esc_html($label),
            );

        if ($layout !== CardLayout::Row && $author->publicEmail() !== '') {
            $html .= sprintf(
                '<a class="ghostlabs-u-card__cta ghostlabs-u-card__cta--secondary" href="%s">%s</a>',
                esc_url('mailto:' . $author->publicEmail()),
                esc_html__('Contact', 'ghostlabs-undercard'),
            );
        }

        return $html . '</p>';
    }

    private function link(Author $author): string
    {
        $url  = $author->url();
        $safe = esc_url($url);

        if ($safe === '') {
            return '';
        }

        return sprintf(
            '<a class="ghostlabs-u-card__link" href="%s">%s</a>',
            $safe,
            esc_html(Display::url($url)),
        );
    }

    private function slot(string $name, string $value, string $tag = 'p'): string
    {
        if ($value === '') {
            return '';
        }

        return sprintf(
            '<%1$s class="ghostlabs-u-card__%2$s">%3$s</%1$s>',
            $tag,
            esc_attr($name),
            esc_html($value),
        );
    }

    private static function attr(array $attributes, string $key, string $default): string
    {
        $value = $attributes[$key] ?? $default;

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
