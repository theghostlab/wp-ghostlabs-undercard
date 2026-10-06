<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum NfcVariation: string
{
    
    case Ember = 'ember';

    case Onyx  = 'onyx';

    public function label(): string
    {
        return match ($this) {
            self::Ember => __('Ember — light', 'ghostlabs-undercard'),
            self::Onyx  => __('Onyx — dark', 'ghostlabs-undercard'),
        };
    }

    public function hasPhotoHeader(): bool
    {
        return $this === self::Onyx;
    }

    public static function fromStored(string $value): self
    {
        return self::tryFrom(strtolower(trim($value))) ?? self::Ember;
    }
}
