<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\FocalPoint;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;
use GHOSTLABS\UNDERCARD\Enum\NfcVariation;
use GHOSTLABS\UNDERCARD\Service\VCardDownload;

final class NfcCard
{
    use CollapsesToChip;

    private const MAX_SOCIAL = 4;

    private static int $panels = 0;

    public function __construct(
        private readonly AvatarResolver $avatars = new AvatarResolver(),
        private readonly QrCode $qrCode = new QrCode(),
    ) {
    }

    public function render(Author $author, array $attributes = []): string
    {
        $layout    = CardLayout::fromStored(self::attr($attributes, 'layout', 'vertical'));
        $variation = NfcVariation::fromStored(self::attr($attributes, 'variation', 'ember'));

        $html = sprintf(
            '<div class="ghostlabs-u-card ghostlabs-u-card--nfc" data-uc-family="nfc"'
            . ' data-uc-variation="%s" data-uc-layout="%s" style="--ghostlabs-u-ratio:%s;%s">',
            esc_attr($variation->value),
            esc_attr($layout->value),
            esc_attr(Geometry::ratio(CardFamily::Nfc, $layout, 0)),
            self::collapsedRatioProperty(CardFamily::Nfc, $layout)
                . $this->photoProperty($author, self::attr($attributes, 'avatar', 'photo'))
                . $this->framingProperties($author, self::attr($attributes, 'avatar', 'photo'), $attributes),
        );

        $html .= '<div class="ghostlabs-u-card__inner">';
        $html .= $this->header($author, $layout, $variation, self::attr($attributes, 'avatar', 'photo'), HeadingLevel::tag($attributes));
        $html .= $this->body($author, $layout);

        return $html . '</div></div>';
    }

    private function header(
        Author $author,
        CardLayout $layout,
        NfcVariation $variation,
        string $variant,
        string $nameTag,
    ): string {
        $html = '<div class="ghostlabs-u-card__header">'
            . $this->portrait($author, $variant);

        if (self::codeBelongsInHeader($layout, $variation)) {
            $html .= $this->qr($author, $layout === CardLayout::Vertical);
        }

        $secondary = $layout === CardLayout::Chip
            ? $this->link($author)
            : $this->slot('title', $this->title($author));

        $html .= '<div class="ghostlabs-u-card__names">'
            . $this->slot('name', $author->name(), $nameTag)
            . $secondary
            . '</div>';

        if ($layout === CardLayout::Horizontal) {
            $html .= '<div class="ghostlabs-u-card__actions">' . $this->save($author) . '</div>';
        }

        return $html . '</div>';
    }

    private static function codeBelongsInHeader(CardLayout $layout, NfcVariation $variation): bool
    {
        return match ($layout) {
            CardLayout::Vertical   => $variation === NfcVariation::Onyx,
            CardLayout::Horizontal => $variation === NfcVariation::Ember,
            default                => false,
        };
    }

    private function body(Author $author, CardLayout $layout): string
    {
        if ($layout === CardLayout::Chip) {
            return '<div class="ghostlabs-u-card__actions">' . $this->save($author, true) . '</div>';
        }

        if ($layout === CardLayout::Row) {
            return '<div class="ghostlabs-u-card__actions">'
                . $this->qr($author, false)
                . $this->save($author)
                . '</div>';
        }

        $actions = $layout === CardLayout::Horizontal
            ? ''
            : '<div class="ghostlabs-u-card__actions">' . $this->save($author) . $this->share($author) . '</div>';

        $html = '<div class="ghostlabs-u-card__body">'
            . $actions
            . $this->bio($author)
            . $this->social($author)
            . $this->link($author)
            . '</div>';

        return $html;
    }

    private function photoProperty(Author $author, string $variant): string
    {
        if ($variant === 'monogram') {
            return '';
        }

        $avatar = $this->avatars->resolve($author);

        if (! $avatar->isImage()) {
            return '';
        }

        $url = esc_url($avatar->value);

        if ($url === '' || preg_match('#[\'"()\\\\\s]#', $url) === 1) {
            return '';
        }

        return "--ghostlabs-u-photo:url('" . $url . "');";
    }

    private function framingProperties(Author $author, string $variant, array $attributes): string
    {
        if ($variant === 'monogram' || ! $this->avatars->resolve($author)->isImage()) {
            return '';
        }

        $props = [];
        $focal = FocalPoint::fromAttribute($attributes['focalPoint'] ?? null);

        if ($focal !== null) {

            $props[] = '--ghostlabs-u-focal:' . $focal->toObjectPosition();
            $props[] = '--ghostlabs-u-focal-x:' . $focal->x;
            $props[] = '--ghostlabs-u-focal-y:' . $focal->y;
        }

        $scale = FocalPoint::scale($attributes['photoScale'] ?? 1);

        if ($scale !== 1.0) {
            $props[] = '--ghostlabs-u-photo-scale:' . $scale;
        }

        return $props === [] ? '' : implode(';', $props) . ';';
    }

    private function portrait(Author $author, string $variant): string
    {
        $avatar = $variant === 'monogram'
            ? new ResolvedAvatar(AvatarSource::Initials, $author->initials())
            : $this->avatars->resolve($author);

        $html = sprintf(
            '<figure class="ghostlabs-u-card__photo" data-uc-avatar="%s">',
            esc_attr(strtolower($avatar->source->name)),
        );

        if ($avatar->isImage()) {

            $html .= sprintf(
                '<img class="ghostlabs-u-card__photo-image" src="%s" alt=""'
                . ' width="390" height="390" decoding="async" />',
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

    private function title(Author $author): string
    {
        return implode(' · ', array_filter([$author->jobTitle(), $author->employer()]));
    }

    private function bio(Author $author): string
    {
        $bio = $author->bio();

        if ($bio === '') {
            return '';
        }

        $url = esc_url($author->profileUrl());

        $text = '<p class="ghostlabs-u-card__bio-label">'
            . esc_html__('Bio', 'ghostlabs-undercard') . '</p>';

        if ($url === '') {
            return $text . '<p class="ghostlabs-u-card__bio">' . esc_html($bio) . '</p>';
        }

        return $text . sprintf(
            '<a class="ghostlabs-u-card__bio" href="%s">%s</a>',
            $url,
            esc_html($bio),
        );
    }

    private function social(Author $author): string
    {
        $profiles = array_slice($author->socialProfiles(), 0, self::MAX_SOCIAL);

        if ($profiles === []) {
            return '';
        }

        $html = '<p class="ghostlabs-u-card__social-label">'
            . esc_html__('Social network', 'ghostlabs-undercard') . '</p>'
            . '<ul class="ghostlabs-u-card__social">';

        foreach ($profiles as $profile) {
            $html .= '<li><a class="ghostlabs-u-card__pip" href="' . esc_url($profile->url()) . '">'
                . '<span aria-hidden="true">' . esc_html($profile->shortCode()) . '</span>'
                . '<span class="screen-reader-text">' . esc_html($profile->displayName()) . '</span>'
                . '</a></li>';
        }

        return $html . '</ul>';
    }

    private function save(Author $author, bool $glyphOnly = false): string
    {
        $vcard = esc_url(VCardDownload::urlFor($author));
        $url   = $vcard !== '' ? $vcard : esc_url($author->profileUrl());

        if ($url === '') {
            return '';
        }

        $download = $vcard !== '' ? ' download' : '';

        $label = __('Save contact', 'ghostlabs-undercard');

        $inner = $glyphOnly
            ? Icons::download() . '<span class="screen-reader-text">' . esc_html($label) . '</span>'
            : esc_html($label);

        return sprintf(
            '<a class="ghostlabs-u-card__action ghostlabs-u-card__action--save" href="%s"'
            . ' data-uc-action="vcard"%s>%s</a>',
            $url,
            $download,
            $inner,
        );
    }

    private function share(Author $author): string
    {
        $url = esc_url($author->profileUrl());

        if ($url === '') {
            return '';
        }

        return sprintf(
            '<a class="ghostlabs-u-card__action ghostlabs-u-card__action--share" href="%s"'
            . ' data-uc-action="share">%s</a>',
            $url,
            esc_html__('Share', 'ghostlabs-undercard'),
        );
    }

    private function qr(Author $author, bool $keepsChip): string
    {
        $url = esc_url($author->profileUrl());

        if ($url === '') {
            return '';
        }

        $code = $this->qrCode->svg($author->profileUrl(), 'ghostlabs-u-card__qr-code');

        $marks = Icons::qrCode('ghostlabs-u-card__qr-mark')
            . (! $keepsChip ? '' : Icons::close('ghostlabs-u-card__qr-dismiss'));

        if ($code === '') {
            return sprintf(
                '<a class="ghostlabs-u-card__qr" href="%s" data-uc-action="qr">%s'
                . '<span class="screen-reader-text">%s</span></a>',
                $url,
                $marks,
                esc_html__('Profile link', 'ghostlabs-undercard'),
            );
        }

        $panelId = 'ghostlabs-u-qr-' . ++self::$panels;

        $chip = sprintf(
            '<a class="ghostlabs-u-card__qr" href="%s" data-uc-action="qr"'
            . ' aria-expanded="false" aria-controls="%s">%s'
            . '<span class="screen-reader-text">%s</span></a>',
            $url,
            esc_attr($panelId),
            $marks,
            esc_html__('Profile link', 'ghostlabs-undercard'),
        );

        $panel = sprintf(
            '<div class="ghostlabs-u-card__qr-panel" id="%s" hidden>'
            . '<div class="ghostlabs-u-card__qr-frame">%s</div>'
            . '<p class="screen-reader-text">%s</p>'
            . '</div>',
            esc_attr($panelId),
            $code,
            esc_html(sprintf(
                /* translators: %s: the author's profile address. */
                __('Scan to open this profile: %s', 'ghostlabs-undercard'),
                Display::url($url),
            )),
        );

        return $chip . $panel . sprintf(
            '<dialog class="ghostlabs-u-card__qr-dialog" aria-label="%s">'
            . '<div class="ghostlabs-u-card__qr-frame">%s</div>'
            . '<p class="ghostlabs-u-card__qr-caption">%s</p>'
            . '<button type="button" class="ghostlabs-u-card__qr-close" data-uc-action="qr-close">%s</button>'
            . '</dialog>',
            esc_attr__('Profile code', 'ghostlabs-undercard'),
            $code,
            esc_html__('Scan to open this profile', 'ghostlabs-undercard'),
            esc_html__('Close', 'ghostlabs-undercard'),
        );
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
