<?php

namespace GHOSTLABS\UNDERCARD\Entity;

use GHOSTLABS\UNDERCARD\Enum\AuthorRole;

final class Assignment
{
    
    public const MAX = 20;

    private const MAX_SLUG = 185;

    private function __construct(
        public readonly string $authorId,
        public readonly AuthorRole $role,
    ) {
    }

    public static function fromArray(mixed $value): ?self
    {
        if (! is_array($value) || ! isset($value['id']) || ! is_string($value['id'])) {
            return null;
        }

        $id = trim($value['id']);

        if (! self::isStableId($id)) {
            return null;
        }

        $role = $value['role'] ?? '';

        return new self($id, is_string($role) ? AuthorRole::fromStored($role) : AuthorRole::Author);
    }

    public static function listFrom(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $assignments = [];
        $seen        = [];

        foreach ($value as $entry) {
            $assignment = self::fromArray($entry);

            if ($assignment === null || isset($seen[$assignment->authorId])) {
                continue;
            }

            $seen[$assignment->authorId] = true;
            $assignments[]               = $assignment;

            if (count($assignments) === self::MAX) {
                break;
            }
        }

        return $assignments;
    }

    private static function isStableId(string $id): bool
    {
        [$kind, $identifier] = array_pad(explode(':', $id, 2), 2, '');

        return match ($kind) {
            'user'  => (bool) preg_match('/^[1-9][0-9]*$/', $identifier),
            'guest' => $identifier !== ''
                && strlen($identifier) <= self::MAX_SLUG
                && preg_match('/\s/', $identifier) !== 1,
            default => false,
        };
    }
}
