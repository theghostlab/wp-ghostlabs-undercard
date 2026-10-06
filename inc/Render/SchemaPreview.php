<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Config\TierMatrix;
use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\SocialProfile;

final class SchemaPreview
{
    
    public function __construct(
        private readonly Author $author,
        private readonly ?bool $credentialsEmitted = null,
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
                'isPro'    => in_array($property, TierMatrix::CREDENTIAL_PROPERTIES, true),
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

        $add('sameAs', implode(', ', array_map(
            static fn (SocialProfile $profile): string => $profile->url(),
            $this->author->socialProfiles()
        )));

        $add('telephone', $this->author->phone());
        $add('email', $this->author->publicEmail());
        $add('address', $this->address());

        return $rows;
    }

    public function hasUnpublishedCredentials(): bool
    {
        if ($this->credentialsAreEmitted()) {
            return false;
        }

        foreach ($this->rows() as $row) {
            if ($row['isPro']) {
                return true;
            }
        }

        return false;
    }

    public function credentialsAreEmitted(): bool
    {
        return $this->credentialsEmitted ?? PluginConfig::tier() !== 'free';
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
