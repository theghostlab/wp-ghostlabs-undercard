<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum FieldType: string
{
    case Text  = 'text';
    case Url   = 'url';
    case Email = 'email';
    case Year  = 'year';

    case Boolean = 'boolean';

    case Terms = 'terms';

    case Urls  = 'urls';

    case Attachment = 'attachment';

    public function isList(): bool
    {
        return $this === self::Terms || $this === self::Urls;
    }

    public function restType(): string
    {
        return match (true) {
            $this->isList()             => 'array',
            $this === self::Attachment  => 'integer',
            $this === self::Boolean     => 'boolean',
            default                     => 'string',
        };
    }

    public function inputType(): string
    {
        return match ($this) {
            self::Url        => 'url',
            self::Email      => 'email',
            self::Year       => 'number',
            self::Boolean    => 'checkbox',
            
            self::Attachment => 'hidden',
            default          => 'text',
        };
    }
}
