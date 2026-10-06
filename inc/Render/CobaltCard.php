<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\FocalPoint;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;
use GHOSTLABS\UNDERCARD\Enum\CobaltVariation;

final class CobaltCard
{
    use CollapsesToChip;

    private const EYEBROW_MAX = 22;

    private const HEX_COLOR = '/^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i';

    public function __construct(
        private readonly AvatarResolver $avatars = new AvatarResolver(),
    ) {
    }

    public function render(Author $author, array $attributes = []): string
    {
        $layout    = CardLayout::fromStored(self::attr($attributes, 'layout', 'vertical'));
        $variation = CobaltVariation::fromStored(self::attr($attributes, 'variation', 'ii'));
        $rows      = $this->rows($author, $layout, $variation);

        $ratio = Geometry::ratio(
            CardFamily::Cobalt,
            $layout,
            count($rows),
            $variation->fixesCardHeight(),
        );

        $html = sprintf(
            '<div class="ghostlabs-u-card ghostlabs-u-card--cobalt" data-uc-family="cobalt"'
            . ' data-uc-variation="%s" data-uc-layout="%s"%s'
            . ' style="--ghostlabs-u-ratio:%s;%s--ghostlabs-u-accent:%s">',
            esc_attr($variation->value),
            esc_attr($layout->value),

            $this->accentIsCustom($attributes) ? ' data-uc-accent="custom"' : '',
            esc_attr($ratio),
            self::collapsedRatioProperty(CardFamily::Cobalt, $layout),
            esc_attr($this->accent($attributes, $variation)),
        );

        $html .= '<div class="ghostlabs-u-card__inner">';
        $html .= $this->body(
            $author,
            $layout,
            $rows,
            self::attr($attributes, 'avatar', 'photo'),
            $attributes['focalPoint'] ?? null,
            FocalPoint::scale($attributes['photoScale'] ?? 1),
            HeadingLevel::tag($attributes),
        );

        return $html . '</div></div>';
    }

    private function body(Author $author, CardLayout $layout, array $rows, string $avatar, mixed $focalPoint, float $scale, string $nameTag): string
    {
        if ($layout === CardLayout::Chip) {
            return $this->plane($author, $avatar, false, $focalPoint, $scale)
                . $this->slot('name', $author->name(), $nameTag)
                . $this->site($author);
        }

        if ($layout === CardLayout::Row) {
            return $this->plane($author, $avatar, false, $focalPoint, $scale)
                . $this->slot('name', $author->name(), $nameTag)
                . $this->slot('role', $this->role($author))
                . $this->contactCluster($rows)
                . $this->site($author);
        }

        return $this->plane($author, $avatar, true, $focalPoint, $scale)
            . '<div class="ghostlabs-u-card__panel">'
            . $this->eyebrow($author)
            . $this->slot('name', $author->name(), $nameTag)
            . $this->slot('role', $this->role($author))
            . $this->rowList($rows)
            . $this->site($author)
            . '</div>';
    }

    private static function planeStyle(?FocalPoint $focal, float $scale): string
    {
        $props = [];

        if ($focal !== null) {

            $props[] = '--ghostlabs-u-focal:' . $focal->toObjectPosition();
            $props[] = '--ghostlabs-u-focal-x:' . $focal->x;
            $props[] = '--ghostlabs-u-focal-y:' . $focal->y;
        }

        if ($scale !== 1.0) {
            $props[] = '--ghostlabs-u-photo-scale:' . $scale;
        }

        return $props === []
            ? ''
            : ' style="' . esc_attr(implode(';', $props)) . '"';
    }

    private function plane(Author $author, string $variant, bool $full, mixed $focalPoint, float $scale): string
    {

        $avatar = $variant === 'monogram'
            ? new ResolvedAvatar(AvatarSource::Initials)
            : $this->avatars->resolve($author);

        $focal = $avatar->isImage() ? FocalPoint::fromAttribute($focalPoint) : null;

        $html = sprintf(
            '<figure class="ghostlabs-u-card__photo" data-uc-avatar="%s"%s>',
            esc_attr(strtolower($avatar->source->name)),
            self::planeStyle($focal, $scale),
        );

        if ($avatar->isImage()) {

            $html .= sprintf(
                '<img class="ghostlabs-u-card__photo-image" src="%s" alt=""'
                . ' width="480" height="480" decoding="async" />',
                esc_url($avatar->value),
            );
        }

        $html .= '</figure>';

        $mark = $full || ! $avatar->isImage()
            ? $this->monogram($author)
            : '';

        return $html . $mark . ($full ? $this->est($author) : '');
    }

    private function monogram(Author $author): string
    {
        $initials = $author->initials();

        if ($initials === '') {
            return '<p class="ghostlabs-u-card__monogram ghostlabs-u-card__monogram--icon"'
                . ' aria-hidden="true">' . Icons::person() . '</p>';
        }

        return '<p class="ghostlabs-u-card__monogram" aria-hidden="true">'
            . esc_html($initials) . '</p>';
    }

    private function est(Author $author): string
    {
        $year = $author->estYear();

        if ($year === '') {
            return '';
        }

        return '<p class="ghostlabs-u-card__est">' . esc_html(sprintf(
            /* translators: %s: the year the author or their practice was established. */
            __('Est. %s', 'ghostlabs-undercard'),
            $year,
        )) . '</p>';
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

    private function role(Author $author): string
    {
        return implode(', ', array_filter([$author->jobTitle(), $author->employer()]));
    }

    private function site(Author $author): string
    {
        $url  = $author->url();
        $safe = esc_url($url);

        if ($safe === '') {
            return '';
        }

        return sprintf(
            '<a class="ghostlabs-u-card__site" href="%s">%s</a>',
            $safe,
            esc_html(Display::url($url)),
        );
    }

    private function rows(Author $author, CardLayout $layout, CobaltVariation $variation): array
    {
        if ($layout === CardLayout::Chip) {
            return [];
        }

        if ($layout === CardLayout::Row) {
            $email = $author->publicEmail();

            return $email === ''
                ? []
                : [['key' => 'email', 'label' => __('Email', 'ghostlabs-undercard'), 'value' => $email]];
        }

        $candidates = [
            ['key' => 'phone', 'label' => __('Mobile', 'ghostlabs-undercard'), 'value' => $author->phone()],
            ['key' => 'email', 'label' => __('Email', 'ghostlabs-undercard'), 'value' => $author->publicEmail()],
            ['key' => 'street', 'label' => __('Studio', 'ghostlabs-undercard'), 'value' => $author->street()],
            ['key' => 'city', 'label' => __('City', 'ghostlabs-undercard'), 'value' => $this->location($author)],
        ];

        $rows = array_values(array_filter(
            $candidates,
            static fn(array $row): bool => $row['value'] !== '',
        ));

        $priority = $variation->contactPriority($layout);
        $drawn    = array_column($candidates, 'key');

        $by = static fn(array $order): callable => static fn(array $a, array $b): int
            => array_search($a['key'], $order, true) <=> array_search($b['key'], $order, true);

        usort($rows, $by($priority));
        $kept = array_slice($rows, 0, $variation->contactCapacity($layout));
        usort($kept, $by($drawn));

        return $kept;
    }

    private function location(Author $author): string
    {
        return implode(' · ', array_filter([$author->locality(), $author->country()]));
    }

    private function rowList(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = '<dl class="ghostlabs-u-card__rows">';

        foreach ($rows as $row) {

            $html .= '<div class="ghostlabs-u-card__row" data-uc-row="' . esc_attr($row['key']) . '">'
                . '<dt class="ghostlabs-u-card__row-label">' . esc_html($row['label']) . '</dt>'
                . '<dd class="ghostlabs-u-card__row-value">' . esc_html($row['value']) . '</dd>'
                . '</div>';
        }

        return $html . '</dl>';
    }

    private function contactCluster(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = '<div class="ghostlabs-u-card__contact">';

        foreach ($rows as $row) {
            $html .= '<span class="ghostlabs-u-card__contact-value">'
                . esc_html($row['value']) . '</span>';
        }

        return $html . '</div>';
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

    private function accent(array $attributes, CobaltVariation $variation): string
    {
        return $this->accentIsCustom($attributes)
            ? self::attr($attributes, 'accent', '')
            : $variation->defaultAccent();
    }

    private function accentIsCustom(array $attributes): bool
    {
        return preg_match(self::HEX_COLOR, self::attr($attributes, 'accent', '')) === 1;
    }

    private static function attr(array $attributes, string $key, string $default): string
    {
        $value = $attributes[$key] ?? $default;

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
