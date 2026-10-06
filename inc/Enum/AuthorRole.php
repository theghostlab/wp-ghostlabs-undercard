<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum AuthorRole: string
{
    case Author      = 'author';
    case CoAuthor    = 'co-author';
    case Editor      = 'editor';
    case Reviewer    = 'reviewer';
    case FactChecker = 'fact-checker';

    public function schemaProperty(): string
    {
        return match ($this) {
            self::Author, self::CoAuthor      => 'author',
            self::Editor                      => 'editor',
            self::Reviewer, self::FactChecker => 'contributor',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Author      => __('Author', 'ghostlabs-undercard'),
            self::CoAuthor    => __('Co-author', 'ghostlabs-undercard'),
            self::Editor      => __('Editor', 'ghostlabs-undercard'),
            self::Reviewer    => __('Reviewer', 'ghostlabs-undercard'),
            self::FactChecker => __('Fact-checker', 'ghostlabs-undercard'),
        };
    }

    public static function fromStored(string $value): self
    {
        return self::tryFrom(trim($value)) ?? self::Author;
    }
}
