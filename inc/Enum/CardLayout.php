<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum CardLayout: string
{
    case Vertical   = 'vertical';
    case Horizontal = 'horizontal';

    case Mini       = 'mini';

    case Row        = 'row';
    case Chip       = 'chip';

    public function label(): string
    {
        return match ($this) {
            self::Vertical   => __('Vertical', 'ghostlabs-undercard'),
            self::Horizontal => __('Horizontal', 'ghostlabs-undercard'),
            self::Mini       => __('Mini', 'ghostlabs-undercard'),
            self::Row        => __('Row', 'ghostlabs-undercard'),
            self::Chip       => __('Chip', 'ghostlabs-undercard'),
        };
    }

    public function isCompact(): bool
    {
        return $this === self::Row || $this === self::Chip;
    }

    public static function fromStored(string $value): self
    {
        return self::tryFrom(trim($value)) ?? self::Vertical;
    }
}
