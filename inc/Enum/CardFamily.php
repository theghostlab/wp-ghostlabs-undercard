<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum CardFamily: string
{
    case Meridian = 'meridian';
    case Cobalt   = 'cobalt';
    case Aurora   = 'aurora';
    case Nfc      = 'nfc';

    public function label(): string
    {
        return match ($this) {
            self::Meridian => __('Meridian', 'ghostlabs-undercard'),
            self::Cobalt   => __('Cobalt', 'ghostlabs-undercard'),
            self::Aurora   => __('Aurora', 'ghostlabs-undercard'),
            self::Nfc      => __('NFC Profile', 'ghostlabs-undercard'),
        };
    }

    public function typefaces(): array
    {
        return match ($this) {
            self::Meridian => ['cormorant', 'cormorant-italic', 'karla'],
            self::Cobalt   => ['league-spartan', 'ibm-plex-sans'],
            self::Aurora   => ['sora'],
            self::Nfc      => ['inter'],
        };
    }

    public function layouts(): array
    {
        return match ($this) {
            self::Aurora => [
                CardLayout::Vertical,
                CardLayout::Horizontal,
                CardLayout::Mini,
                CardLayout::Row,
                CardLayout::Chip,
            ],
            default => [
                CardLayout::Vertical,
                CardLayout::Horizontal,
                CardLayout::Row,
                CardLayout::Chip,
            ],
        };
    }

    public function fontHandle(): string
    {
        return 'undercard-font-' . $this->value;
    }
}
