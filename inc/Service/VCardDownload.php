<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Render\VCard;
use InvalidArgumentException;
use WP_User;

final class VCardDownload
{
    public const QUERY_VAR = 'undercard_vcard';

    public function __construct(
        private readonly VCard $vcard = new VCard(),
    ) {
    }

    public function register(): void
    {
        add_filter('query_vars', [$this, 'queryVars']);
        add_action('template_redirect', [$this, 'maybeServe']);
    }

    public function queryVars(array $vars): array
    {
        $vars[] = self::QUERY_VAR;

        return $vars;
    }

    public static function urlFor(Author $author): string
    {
        if (! self::servesCardFor($author)) {
            return '';
        }

        return (string) add_query_arg(self::QUERY_VAR, (string) $author->userId(), home_url('/'));
    }

    public static function servesCardFor(Author $author): bool
    {
        if ($author->userId() === 0 || $author->optsOutOfContactCard()) {
            return false;
        }

        if (! $author->hasContactDetails()) {
            return false;
        }

        return (bool) apply_filters('ghostlabs_undercard_vcard_enabled', true, $author);
    }

    public function maybeServe(): void
    {
        $requested = get_query_var(self::QUERY_VAR);

        if ($requested === '' || $requested === null || $requested === false) {
            return;
        }

        $author = $this->resolve((int) $requested);

        if ($author === null) {
            return;
        }

        $body = $this->vcard->forAuthor($author);

        $this->sendHeaders($this->vcard->filenameFor($author), strlen($body));

        echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        exit;
    }

    private function resolve(int $userId): ?Author
    {
        if ($userId <= 0) {
            return null;
        }

        $user = get_userdata($userId);

        if (! $user instanceof WP_User) {
            return null;
        }

        if (count_user_posts($userId, 'post', true) < 1) {
            return null;
        }

        try {
            $author = Author::fromWpUser($user);
        } catch (InvalidArgumentException) {
            
            return null;
        }

        return self::servesCardFor($author) ? $author : null;
    }

    private function sendHeaders(string $filename, int $length): void
    {
        nocache_headers();

        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $length);

        header('X-Content-Type-Options: nosniff');
    }
}
