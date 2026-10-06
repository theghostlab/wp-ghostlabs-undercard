<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\Credit;
use GHOSTLABS\UNDERCARD\Service\PostAuthors;
use GHOSTLABS\UNDERCARD\Service\PublicPresence;
use InvalidArgumentException;
use WP_Post;
use WP_User;

final class CardAuthor
{
    public function __construct(private readonly PostAuthors $postAuthors)
    {
    }

    public function creditsForBlock(array $attributes, int $postId = 0): array
    {
        $explicit = (int) ($attributes['authorId'] ?? 0);

        if ($explicit > 0) {
            $author = $this->fromUserId($explicit);

            return $author instanceof Author ? [ new Credit($author) ] : [];
        }

        $postId = $this->resolvePostId($postId);

        return $postId > 0 ? $this->postAuthors->bylineFor($postId) : [];
    }

    public function forBlock(array $attributes, int $postId = 0): ?Author
    {

        $explicit = (int) ($attributes['authorId'] ?? 0);

        if ($explicit > 0) {
            return $this->fromUserId($explicit);
        }

        $postId = $this->resolvePostId($postId);

        if ($postId <= 0) {
            return null;
        }

        return $this->postAuthors->for($postId)[0] ?? null;
    }

    private function resolvePostId(int $postId): int
    {
        if ($postId > 0) {
            return $postId;
        }

        $post = get_post();

        return $post instanceof WP_Post ? (int) $post->ID : 0;
    }

    private function fromUserId(int $userId): ?Author
    {
        $user = get_userdata($userId);

        if (! $user instanceof WP_User) {
            return null;
        }

        if (! (new PublicPresence($this->postAuthors))->allows($user)) {
            return null;
        }

        try {
            return Author::fromWpUser($user);
        } catch (InvalidArgumentException) {
            
            return null;
        }
    }
}
