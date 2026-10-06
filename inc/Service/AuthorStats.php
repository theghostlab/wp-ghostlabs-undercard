<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;

final class AuthorStats
{
    private const SECONDS_PER_YEAR = 31556952; 

    public function __construct(
        private readonly ?int $now = null,
        private readonly ?PostAuthors $postAuthors = null,
    ) {
    }

    public function for(Author $author): array
    {
        
        if ($author->isGuest()) {
            return [];
        }

        $userId = $author->userId();

        $stats = [
            new AuthorStat('posts', __('Posts', 'ghostlabs-undercard'), $this->compact($this->postCount($userId))),
            new AuthorStat('comments', __('Comments', 'ghostlabs-undercard'), $this->compact($this->commentCount($userId))),
        ];

        $years = $this->wholeYearsSince($author->registeredAt());

        if ($years !== null && $years > 0) {
            $stats[] = new AuthorStat('years', __('Years', 'ghostlabs-undercard'), $this->compact($years));
        }

        return $stats;
    }

    private function postCount(int $userId): int
    {
        if (! apply_filters('ghostlabs_undercard_archive_follows_byline', true)) {
            return (int) count_user_posts($userId, 'post', true);
        }

        $postAuthors = $this->postAuthors ?? new PostAuthors($GLOBALS['wpdb']);

        return $postAuthors->bylinedPostCount($userId);
    }

    private function commentCount(int $userId): int
    {
        $count = get_comments([
            'user_id' => $userId,
            'status'  => 'approve',
            'count'   => true,
        ]);

        return is_numeric($count) ? (int) $count : 0;
    }

    private function wholeYearsSince(string $registeredAt): ?int
    {
        if (trim($registeredAt) === '') {
            return null;
        }

        $registered = strtotime($registeredAt);

        if ($registered === false) {
            return null;
        }

        $now = $this->now ?? time();

        return (int) floor(($now - $registered) / self::SECONDS_PER_YEAR);
    }

    private function compact(int $value): string
    {
        if ($value < 1000) {
            return (string) $value;
        }

        [$divisor, $suffix] = $value < 1000000 ? [1000, 'k'] : [1000000, 'M'];

        $scaled = $value / $divisor;

        return rtrim(rtrim(number_format($scaled, 1, '.', ''), '0'), '.') . $suffix;
    }
}
