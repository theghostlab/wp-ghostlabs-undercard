<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\AvatarCrop;
use GHOSTLABS\UNDERCARD\Service\GuestAuthors;
use GHOSTLABS\UNDERCARD\Service\Loader;

final class AvatarCropProvider implements FeatureProviderInterface
{
    private const USER_PHOTO = 'undercard_avatar_id';

    private const GUEST_PHOTO = '_thumbnail_id';

    public function __construct(
        private readonly AvatarCrop $crop = new AvatarCrop(),
    ) {
    }

    public function register(Loader $loader): void
    {
        foreach (['added_user_meta', 'updated_user_meta'] as $hook) {
            $loader->add_action($hook, $this, 'onUserMeta', 10, 4);
        }
        foreach (['added_post_meta', 'updated_post_meta'] as $hook) {
            $loader->add_action($hook, $this, 'onPostMeta', 10, 4);
        }
    }

    public function onUserMeta(int $metaId, int $userId, string $key, mixed $value): void
    {
        if ($key === self::USER_PHOTO && (int) $value > 0) {
            $this->crop->ensure((int) $value);
        }
    }

    public function onPostMeta(int $metaId, int $postId, string $key, mixed $value): void
    {
        if ($key === self::GUEST_PHOTO && (int) $value > 0 && get_post_type($postId) === GuestAuthors::POST_TYPE) {
            $this->crop->ensure((int) $value);
        }
    }
}
