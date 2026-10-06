<?php

namespace GHOSTLABS\UNDERCARD\Entity;

use GHOSTLABS\UNDERCARD\Enum\SocialPlatform;

final class SocialProfile
{
    private function __construct(
        private readonly SocialPlatform $platform,
        private readonly string $url,
        private readonly string $host,
    ) {
    }

    public static function fromUrl(string $url): ?self
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (preg_match('#^([a-z][a-z0-9+.\-]*):#i', $url, $matches) === 1) {
            $scheme = strtolower($matches[1]);

            if ($scheme !== 'http' && $scheme !== 'https') {
                return null;
            }
        } else {
            $url = 'https://' . $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = wp_parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);
        $bare = preg_replace('/^www\./', '', $host) ?? $host;

        return new self(SocialPlatform::fromHost($bare), $url, $bare);
    }

    public static function fromUrls(iterable $urls): array
    {
        $profiles = [];
        $seen     = [];

        foreach ($urls as $url) {
            if (! is_string($url)) {
                continue;
            }

            $profile = self::fromUrl($url);

            if ($profile === null || isset($seen[$profile->url])) {
                continue;
            }

            $seen[$profile->url] = true;
            $profiles[]          = $profile;
        }

        return $profiles;
    }

    public function platform(): SocialPlatform
    {
        return $this->platform;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function shortCode(): string
    {
        if ($this->platform !== SocialPlatform::Other) {
            return $this->platform->shortCode();
        }

        return mb_strtolower(mb_substr($this->host, 0, 2));
    }

    public function displayName(): string
    {
        return $this->platform === SocialPlatform::Other
            ? $this->host
            : $this->platform->displayName();
    }
}
