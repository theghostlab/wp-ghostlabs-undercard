<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Entity\Author;

final class SchemaPreview
{
    public function __construct(
        private readonly Author $author,
    ) {
    }

    public function rows(): array
    {
        $rows = [];

        $add = function (string $property, string $value) use (&$rows): void {
            if ($value === '') {
                return;
            }

            $rows[] = [
                'property' => $property,
                'value'    => $value,
            ];
        };

        $add('@type', 'Person');
        $add('@id', $this->author->schemaId());
        $add('name', $this->author->name());
        $add('url', $this->author->profileUrl());
        $add('description', $this->truncate($this->author->bio()));
        $add('image', $this->author->avatarUrl());

        $add('jobTitle', $this->author->jobTitle());
        $add('worksFor', $this->author->employer());
        $add('alumniOf', $this->author->education());
        $add('knowsAbout', implode(', ', $this->author->expertise()));

        $add('sameAs', implode(', ', $this->sameAs()));

        $add('telephone', $this->author->phone());
        $add('email', $this->author->publicEmail());
        $add('address', $this->address());

        return $rows;
    }

    public function hasCredentials(): bool
    {
        foreach ($this->rows() as $row) {
            if (in_array($row['property'], TierMatrix::CREDENTIAL_PROPERTIES, true)) {
                return true;
            }
        }

        return false;
    }

    private function sameAs(): array
    {
        $urls = [];
        $site = $this->author->url();

        if ($site !== '' && $site !== $this->author->profileUrl()) {
            $urls[] = $site;
        }

        foreach ($this->author->socialProfiles() as $profile) {
            $urls[] = $profile->url();
        }

        return array_values(array_unique($urls));
    }

    private function address(): string
    {
        $parts = array_filter([
            $this->author->street(),
            $this->author->locality(),
            $this->author->country(),
        ]);

        return implode(', ', $parts);
    }

    private function truncate(string $value, int $limit = 120): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit - 1) . '…';
    }
}
