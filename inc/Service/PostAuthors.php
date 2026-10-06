<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Assignment;
use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\Credit;
use GHOSTLABS\UNDERCARD\Enum\AuthorRole;
use InvalidArgumentException;
use WP_Post;
use WP_User;
use wpdb;

final class PostAuthors
{
    private const TABLE = 'undercard_post_authors';

    public function __construct(
        private readonly wpdb $db,
        private readonly GuestAuthorsInterface $guests = new GuestAuthors(),
    ) {
    }

    public static function tableName(wpdb $db): string
    {
        return $db->prefix . self::TABLE;
    }

    public function for(int $postId): array
    {
        return Credit::authors($this->bylineFor($postId));
    }

    public function bylineFor(int $postId): array
    {

        return $this->storedCredits($postId)
            ?? array_map(Credit::of(...), $this->coreAuthorFor($postId));
    }

    public function assignedFor(int $postId): array
    {
        return $this->storedCredits($postId) ?? [];
    }

    private function storedCredits(int $postId): ?array
    {
        $rows = $this->rowsFor($postId);

        if ($rows === []) {
            return null;
        }

        $credits = [];

        foreach ($rows as $row) {
            $author = $this->hydrate((string) ($row->author_id ?? ''));

            if ($author instanceof Author) {
                $credits[] = new Credit($author, AuthorRole::fromStored((string) ($row->role ?? '')));
            }
        }

        return $credits;
    }

    public function set(int $postId, array $assignments): void
    {
        if ($postId <= 0) {
            return;
        }

        $table = self::tableName($this->db);

        $this->db->delete($table, ['post_id' => $postId], ['%d']);

        $position = 0;

        foreach ($assignments as $assignment) {
            $this->db->insert(
                $table,
                [
                    'post_id'   => $postId,
                    'author_id' => $assignment->authorId,
                    'position'  => $position++,
                    'role'      => $assignment->role->value,
                ],
                ['%d', '%s', '%d', '%s'],
            );
        }
    }

    public function postsCrediting(string $authorId, int $limit = 100): array
    {
        if ($authorId === '' || $limit < 1) {
            return [];
        }

        $sql = $this->db->prepare(
            'SELECT a.post_id FROM %i AS a'
                . ' INNER JOIN %i AS p ON p.ID = a.post_id'
                . " WHERE a.author_id = %s AND p.post_status = 'publish'"
                . ' ORDER BY p.post_date DESC LIMIT %d',
            self::tableName($this->db),
            $this->db->posts,
            $authorId,
            $limit
        );

        if (! is_string($sql)) {
            return [];
        }

        $ids = array_map('intval', $this->db->get_col($sql));

        return array_values(array_filter($ids, static fn(int $id): bool => $id > 0));
    }

    public function bylinedPostCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $sql = $this->db->prepare(
            'SELECT COUNT(*) FROM %i AS p'
                . " WHERE p.post_type = 'post' AND p.post_status = 'publish'"
                . ' AND ((p.post_author = %d AND NOT EXISTS ('
                . 'SELECT 1 FROM %i AS uc_unassigned WHERE uc_unassigned.post_id = p.ID'
                . ')) OR EXISTS ('
                . 'SELECT 1 FROM %i AS uc_credited WHERE uc_credited.post_id = p.ID'
                . ' AND uc_credited.author_id = %s'
                . '))',
            $this->db->posts,
            $userId,
            self::tableName($this->db),
            self::tableName($this->db),
            'user:' . $userId
        );

        if (! is_string($sql)) {
            return 0;
        }

        return (int) $this->db->get_var($sql);
    }

    private function rowsFor(int $postId): array
    {

        $sql = $this->db->prepare(
            'SELECT author_id, position, role FROM %i WHERE post_id = %d ORDER BY position ASC',
            self::tableName($this->db),
            $postId
        );

        if (! is_string($sql)) {
            return [];
        }

        $rows = $this->db->get_results($sql);

        return is_array($rows) ? $rows : [];
    }

    public function hydrate(string $stableId): ?Author
    {
        $parts = explode(':', $stableId, 2);

        if (count($parts) !== 2 || $parts[1] === '') {
            return null;
        }

        [$kind, $identifier] = $parts;

        return match ($kind) {
            'user'  => $this->authorFromUserId((int) $identifier),
            'guest' => $this->guests->bySlug($identifier),
            default => null,
        };
    }

    private function coreAuthorFor(int $postId): array
    {
        $post = get_post($postId);

        if (! $post instanceof WP_Post) {
            return [];
        }

        $author = $this->authorFromUserId((int) $post->post_author);

        return $author instanceof Author ? [$author] : [];
    }

    private function authorFromUserId(int $userId): ?Author
    {
        $user = get_userdata($userId);

        if (! $user instanceof WP_User) {
            return null;
        }

        try {
            return Author::fromWpUser($user);
        } catch (InvalidArgumentException) {
            
            return null;
        }
    }
}
