<?php

namespace GHOSTLABS\UNDERCARD\Entity;

use GHOSTLABS\UNDERCARD\Enum\AuthorRole;

final class Credit
{
    public function __construct(
        public readonly Author $author,
        public readonly AuthorRole $role = AuthorRole::Author,
    ) {
    }

    public static function of(Author $author, AuthorRole $role = AuthorRole::Author): self
    {
        return new self($author, $role);
    }

    public static function authors(array $credits): array
    {
        return array_map(static fn(self $credit): Author => $credit->author, $credits);
    }
}
