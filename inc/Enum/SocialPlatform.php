<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum SocialPlatform: string
{
    case LinkedIn  = 'linkedin';
    case X         = 'x';
    case Instagram = 'instagram';
    case YouTube   = 'youtube';
    case Facebook  = 'facebook';
    case GitHub    = 'github';
    case Mastodon  = 'mastodon';
    case Bluesky   = 'bluesky';
    case Threads   = 'threads';
    case TikTok    = 'tiktok';
    case Other     = 'other';

    public function shortCode(): string
    {
        return match ($this) {
            self::LinkedIn  => 'in',
            self::X         => 'X',
            self::Instagram => 'ig',
            self::YouTube   => 'yt',
            self::Facebook  => 'fb',
            self::GitHub    => 'gh',
            self::Mastodon  => 'ma',
            self::Bluesky   => 'bs',
            self::Threads   => 'th',
            self::TikTok    => 'tt',
            self::Other     => 'we',
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::LinkedIn  => 'LinkedIn',
            self::X         => 'X',
            self::Instagram => 'Instagram',
            self::YouTube   => 'YouTube',
            self::Facebook  => 'Facebook',
            self::GitHub    => 'GitHub',
            self::Mastodon  => 'Mastodon',
            self::Bluesky   => 'Bluesky',
            self::Threads   => 'Threads',
            self::TikTok    => 'TikTok',
            self::Other     => 'Website',
        };
    }

    public static function fromHost(string $host): self
    {
        $host = strtolower(trim($host));

        return match (true) {
            self::hostMatches($host, 'linkedin.com')                    => self::LinkedIn,
            self::hostMatches($host, 'x.com', 'twitter.com')            => self::X,
            self::hostMatches($host, 'instagram.com')                   => self::Instagram,
            self::hostMatches($host, 'youtube.com', 'youtu.be')         => self::YouTube,
            self::hostMatches($host, 'facebook.com', 'fb.com')          => self::Facebook,
            self::hostMatches($host, 'github.com')                      => self::GitHub,
            self::hostMatches($host, 'bsky.app')                        => self::Bluesky,
            self::hostMatches($host, 'threads.net', 'threads.com')      => self::Threads,
            self::hostMatches($host, 'tiktok.com')                      => self::TikTok,
            
            str_contains($host, 'mastodon')                             => self::Mastodon,
            default                                                     => self::Other,
        };
    }

    private static function hostMatches(string $host, string ...$domains): bool
    {
        foreach ($domains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
