<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum CobaltVariation: string
{
    case II = 'ii';
    case V  = 'v';

    public function label(): string
    {
        return match ($this) {
            self::II => __('Cobalt II — ink panel', 'ghostlabs-undercard'),
            self::V  => __('Cobalt V — ivory panel', 'ghostlabs-undercard'),
        };
    }

    public function defaultAccent(): string
    {
        return match ($this) {
            self::II => '#E30613',
            self::V  => '#0F5A5A',
        };
    }

    public function fixesCardHeight(): bool
    {
        return $this === self::V;
    }

    public function contactCapacity(CardLayout $layout): int
    {
        if ($this === self::V) {
            return $layout === CardLayout::Horizontal ? 2 : 4;
        }

        return 3;
    }

    public function contactPriority(CardLayout $layout): array
    {
        if ($this === self::V && $layout === CardLayout::Horizontal) {
            return ['email', 'city', 'phone', 'street'];
        }

        return ['phone', 'email', 'street', 'city'];
    }

    public static function fromStored(string $value): self
    {
        return self::tryFrom(strtolower(trim($value))) ?? self::II;
    }
}
