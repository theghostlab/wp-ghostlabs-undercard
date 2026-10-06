<?php

namespace GHOSTLABS\UNDERCARD\Config;

use GHOSTLABS\UNDERCARD\Enum\FieldType;

final class ProfileField
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly FieldType $type,
        public readonly string $description = '',
        
        public readonly bool $isContactMethod = false,
        
        public readonly bool $isGuestOnly = false,
        
        public readonly bool $isUserOnly = false,
    ) {
    }

    public function isList(): bool
    {
        return $this->type->isList();
    }

    public function restType(): string
    {
        return $this->type->restType();
    }
}
