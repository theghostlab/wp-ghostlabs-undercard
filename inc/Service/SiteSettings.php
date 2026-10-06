<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GhostLabs\WPKit\Contracts\OptionsServiceInterface;

final class SiteSettings
{
    
    public const BYLINE_POST_TYPES = 'byline_post_types';

    public const GUEST_SLUG = 'guest_slug';

    public const BYLINE_FOLLOWS_CORE = 'byline_follows_core';

    public const VCARD_ENABLED = 'vcard_enabled';

    public const DEFAULT_GUEST_SLUG = 'guest-author';

    private const PRIORITY = 5;

    public function __construct(
        private readonly OptionsServiceInterface $options = new OptionsService(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $loader->add_filter('ghostlabs_undercard_byline_post_types', $this, 'filterBylinePostTypes', self::PRIORITY);
        $loader->add_filter('ghostlabs_undercard_guest_slug', $this, 'filterGuestSlug', self::PRIORITY);
        $loader->add_filter('ghostlabs_undercard_byline_follows_core', $this, 'filterBylineFollowsCore', self::PRIORITY);
        $loader->add_filter('ghostlabs_undercard_vcard_enabled', $this, 'filterVCardEnabled', self::PRIORITY);
    }

    public function filterBylinePostTypes(array $supported): array
    {
        $chosen = $this->chosenPostTypes();

        if ($chosen === null) {
            return $supported;
        }

        return array_values(array_intersect($supported, $chosen));
    }

    public function filterGuestSlug(string $slug): string
    {
        $stored = $this->stored(self::GUEST_SLUG);

        if ($stored === null || $stored === '' || GuestBase::owner($stored, GuestBase::SCOPE_CORE) !== null) {
            return $slug;
        }

        return $stored;
    }

    public function filterBylineFollowsCore(bool $follows): bool
    {
        return $this->flag(self::BYLINE_FOLLOWS_CORE) ?? $follows;
    }

    public function filterVCardEnabled(bool $enabled): bool
    {
        return $this->flag(self::VCARD_ENABLED) ?? $enabled;
    }

    public function values(array $eligible): array
    {
        return [
            'bylinePostTypes'   => $this->filterBylinePostTypes($eligible),
            'guestSlug'         => $this->filterGuestSlug(self::DEFAULT_GUEST_SLUG),
            'bylineFollowsCore' => $this->filterBylineFollowsCore(true),
            'vcardEnabled'      => $this->filterVCardEnabled(true),
        ];
    }

    private function chosenPostTypes(): ?array
    {
        $stored = $this->stored(self::BYLINE_POST_TYPES);

        if ($stored === null) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $stored))));
    }

    private function flag(string $key): ?bool
    {
        $stored = $this->stored($key);

        return $stored === null ? null : $stored === '1';
    }

    private function stored(string $key): ?string
    {
        $value = $this->options->get($key);

        return is_string($value) ? $value : null;
    }
}
