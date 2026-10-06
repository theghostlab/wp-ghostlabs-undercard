<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;

final class GravatarProbe implements AvatarProbeInterface
{
    private const PREFIX = 'ghostlabs_undercard_gravatar_';

    private const TTL_FOUND   = WEEK_IN_SECONDS;
    private const TTL_MISSING = DAY_IN_SECONDS;

    private const TIMEOUT = 2;

    public function exists(Author $author): bool
    {
        
        if ($author->isGuest() || $author->userId() === 0) {
            return false;
        }

        if (! get_option('show_avatars')) {
            return false;
        }

        $url = $author->gravatarUrl();

        if ($url === '') {
            return false;
        }

        $key    = self::PREFIX . md5($url);
        $cached = get_transient($key);

        if ($cached === '1' || $cached === '0') {
            return $cached === '1';
        }

        $found = $this->ask($url);

        set_transient($key, $found ? '1' : '0', $found ? self::TTL_FOUND : self::TTL_MISSING);

        return $found;
    }

    private function ask(string $url): bool
    {
        $response = wp_remote_head(
            add_query_arg('d', '404', $url),
            ['timeout' => self::TIMEOUT, 'redirection' => 1],
        );

        if (is_wp_error($response)) {
            return false;
        }

        return wp_remote_retrieve_response_code($response) === 200;
    }

    public function forget(Author $author): void
    {
        $url = $author->gravatarUrl();

        if ($url !== '') {
            delete_transient(self::PREFIX . md5($url));
        }
    }
}
