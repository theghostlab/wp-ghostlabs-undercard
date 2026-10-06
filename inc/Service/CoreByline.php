<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\Credit;
use WP_Block;

final class CoreByline
{
    
    private const BLOCKS = [
        'core/post-author-name',
        'core/post-author',
    ];

    public function __construct(private readonly PostAuthors $postAuthors)
    {
    }

    public function register(): void
    {
        foreach (self::BLOCKS as $block) {
            add_filter('render_block_' . $block, [$this, 'follow'], 10, 3);
        }
    }

    public function follow(string $content, array $block, WP_Block $instance): string
    {
        
        if (! apply_filters('ghostlabs_undercard_byline_follows_core', true)) {
            return $content;
        }

        $postId = (int) ($instance->context['postId'] ?? 0);

        if ($postId <= 0 || $content === '') {
            return $content;
        }

        $credits = $this->postAuthors->assignedFor($postId);
        $lead    = $credits[0] ?? null;

        if (! $lead instanceof Credit) {
            return $content;
        }

        $coreAuthorId = (int) get_post_field('post_author', $postId);

        return $this->rewrite(
            $content,
            (string) get_the_author_meta('display_name', $coreAuthorId),
            (string) get_author_posts_url($coreAuthorId),
            $lead->author,
        );
    }

    public function rewrite(string $content, string $fromName, string $fromUrl, Author $to): string
    {
        if ($fromUrl !== '' && $to->profileUrl() !== '') {
            $content = str_replace(esc_url($fromUrl), esc_url($to->profileUrl()), $content);
        }

        if ($fromName !== '' && $to->name() !== '') {
            $content = str_replace(esc_html($fromName), esc_html($to->name()), $content);
        }

        return $content;
    }
}
