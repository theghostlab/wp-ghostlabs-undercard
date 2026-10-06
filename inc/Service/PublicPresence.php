<?php

namespace GHOSTLABS\UNDERCARD\Service;

use WP_User;

final class PublicPresence
{
    
    public function __construct(private readonly ?PostAuthors $postAuthors = null)
    {
    }

    public function allows(WP_User $user): bool
    {

        $allowed = user_can($user, 'edit_posts')
            || ($this->postAuthors ?? new PostAuthors($GLOBALS['wpdb']))->bylinedPostCount((int) $user->ID) > 0;

        return (bool) apply_filters('ghostlabs_undercard_user_is_public', $allowed, $user);
    }
}
