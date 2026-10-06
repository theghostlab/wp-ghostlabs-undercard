<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;

final class MeridianCard
{
    use CollapsesToChip;

    private const EYEBROW_MAX = 22;

    private const MAX_ROWS = 4;

    public function __construct(
        private readonly AvatarResolver $avatars = new AvatarResolver(),
    ) {
    }

    public function render(Author $author, array $attributes = []): string
    {
        $layout = CardLayout::fromStored(self::attr($attributes, 'layout', 'vertical'));
        $scheme = self::attr($attributes, 'scheme', 'dark');
        $rows   = $this->rows($author, $layout);

        $ratio = Geometry::ratio(CardFamily::Meridian, $layout, count($rows));

        $html = sprintf(
            '<div class="ghostlabs-u-card ghostlabs-u-card--meridian" data-uc-family="meridian"'
            . ' data-uc-scheme="%s" data-uc-layout="%s" style="--ghostlabs-u-ratio:%s;%s">',
            esc_attr($scheme),
            esc_attr($layout->value),
            esc_attr($ratio),

            self::collapsedRatioProperty(CardFamily::Meridian, $layout),
        );

        $html .= '<div class="ghostlabs-u-card__inner">';
        $html .= $this->body($author, $layout, $rows, self::attr($attributes, 'avatar', 'photo'), HeadingLevel::tag($attributes));

        return $html . '</div></div>';
    }

    private function body(Author $author, CardLayout $layout, array $rows, string $avatar, string $nameTag): string
    {
        $identity = $this->avatar($author, $avatar)
            . $this->slot('name', $author->name(), $nameTag);

        if ($layout === CardLayout::Chip) {
            return $identity . $this->slot('role', $author->jobTitle());
        }

        if ($layout === CardLayout::Row) {
            return $identity
                . $this->slot('role', $author->jobTitle())
                . $this->contactCluster($rows);
        }

        $detail = $this->eyebrow($author)
            . $this->slot('role', $author->jobTitle())
            . $this->rowList($rows)
            . $this->cta($author);

        if ($layout === CardLayout::Horizontal) {
            return '<div class="ghostlabs-u-card__identity">'
                . $identity
                . $this->slot('firm', $author->employer())
                . '</div>'
                . $this->divider()
                . '<div class="ghostlabs-u-card__detail">' . $detail . '</div>';
        }

        return $this->eyebrow($author)
            . $identity
            . $this->divider()
            . $this->slot('role', $author->jobTitle())
            . $this->slot('firm', $author->employer())
            . $this->rowList($rows)
            . $this->cta($author);
    }

    private function contactCluster(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = '<div class="ghostlabs-u-card__contact">';

        foreach ($rows as $row) {
            $html .= '<span class="ghostlabs-u-card__contact-value">'
                . self::value($row) . '</span>';
        }

        return $html . '</div>';
    }

    private static function value(array $row): string
    {
        $href = esc_url($row['href']);

        if ($href === '') {
            return esc_html($row['value']);
        }

        return '<a class="ghostlabs-u-card__value-link" href="' . $href . '">'
            . esc_html($row['value']) . '</a>';
    }

    private function eyebrow(Author $author): string
    {
        $term = $author->primaryExpertise();

        if ($term === '') {
            return '';
        }

        if (mb_strlen($term) > self::EYEBROW_MAX) {
            $term = rtrim(mb_substr($term, 0, self::EYEBROW_MAX - 1)) . '…';
        }

        return '<p class="ghostlabs-u-card__eyebrow">' . esc_html($term) . '</p>';
    }

    private function avatar(Author $author, string $variant): string
    {
        $avatar = $variant === 'monogram'
            ? new ResolvedAvatar(AvatarSource::Initials, $author->initials())
            : $this->avatars->resolve($author);

        if ($avatar->isImage()) {

            return sprintf(
                '<img class="ghostlabs-u-card__avatar" data-uc-avatar="%s" src="%s" alt=""'
                . ' width="96" height="96" decoding="async" />',
                esc_attr(strtolower($avatar->source->name)),
                esc_url($avatar->value),
            );
        }

        if ($avatar->value !== '') {
            return '<p class="ghostlabs-u-card__monogram" data-uc-avatar="initials" aria-hidden="true">'
                . esc_html($avatar->value) . '</p>';
        }

        return '<p class="ghostlabs-u-card__monogram ghostlabs-u-card__monogram--icon"'
            . ' data-uc-avatar="icon" aria-hidden="true">' . Icons::person() . '</p>';
    }

    private function divider(): string
    {
        return '<p class="ghostlabs-u-card__divider" aria-hidden="true">'
            . '<span class="ghostlabs-u-card__divider-mark"></span>'
            . '</p>';
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

    private function rows(Author $author, CardLayout $layout): array
    {
        if ($layout === CardLayout::Chip) {
            return [];
        }

        $website = [
            'label' => __('Website', 'ghostlabs-undercard'),
            'value' => Display::url($author->url()),
            'href'  => $author->url(),
        ];

        $candidates = [
            [
                'label' => __('Direct', 'ghostlabs-undercard'),
                'value' => $author->phone(),
                'href'  => self::telHref($author->phone()),
            ],
            [
                'label' => __('Email', 'ghostlabs-undercard'),
                'value' => $author->publicEmail(),
                'href'  => $author->publicEmail() !== '' ? 'mailto:' . $author->publicEmail() : '',
            ],
            ['label' => __('Office', 'ghostlabs-undercard'), 'value' => $this->location($author), 'href' => ''],
            $website,
        ];

        if ($layout === CardLayout::Row) {
            $candidates = [$website, ...$candidates];
        }

        $limit = $layout === CardLayout::Row ? 1 : self::MAX_ROWS;

        $rows = array_values(array_filter(
            $candidates,
            static fn(array $row): bool => $row['value'] !== '',
        ));

        return array_slice($rows, 0, $limit);
    }

    private static function telHref(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        return 'tel:' . (str_starts_with(ltrim($phone), '+') ? '+' : '') . $digits;
    }

    private function location(Author $author): string
    {
        $parts = array_filter([$author->locality(), $author->country()]);

        return implode(' · ', $parts);
    }

    private function rowList(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = '<dl class="ghostlabs-u-card__rows">';

        foreach ($rows as $row) {
            $html .= '<div class="ghostlabs-u-card__row">'
                . '<dt class="ghostlabs-u-card__row-label">' . esc_html($row['label']) . '</dt>'
                . '<dd class="ghostlabs-u-card__row-value">' . self::value($row) . '</dd>'
                . '</div>';
        }

        return $html . '</dl>';
    }

    private function cta(Author $author): string
    {
        $custom = $author->ctaUrl() !== '';
        $url    = $custom ? $author->ctaUrl() : $author->profileUrl();

        $label = $author->ctaLabel() !== ''
            ? $author->ctaLabel()
            : ($custom
                ? __('Learn more', 'ghostlabs-undercard')
                : __('View all posts', 'ghostlabs-undercard'));

        $safe = esc_url($url);

        if ($safe === '') {
            return '';
        }

        return sprintf(
            '<a class="ghostlabs-u-card__cta" href="%s">%s</a>',
            $safe,
            esc_html($label),
        );
    }

    private static function attr(array $attributes, string $key, string $default): string
    {
        $value = $attributes[$key] ?? $default;

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
