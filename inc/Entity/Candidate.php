<?php

namespace GHOSTLABS\UNDERCARD\Entity;

final class Candidate
{
    private function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $kind,
        public readonly string $avatarUrl,
    ) {
    }

    public static function user(int $userId, string $name, string $avatarUrl = ''): self
    {
        return new self('user:' . $userId, $name, 'user', $avatarUrl);
    }

    public static function guest(string $slug, string $name, string $avatarUrl = ''): self
    {
        return new self('guest:' . $slug, $name, 'guest', $avatarUrl);
    }

    public static function fromAuthor(Author $author): self
    {
        return new self(
            $author->id(),
            $author->name(),
            $author->isGuest() ? 'guest' : 'user',
            $author->avatarUrl(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'kind'   => $this->kind,
            'avatar' => $this->avatarUrl,
        ];
    }
}
