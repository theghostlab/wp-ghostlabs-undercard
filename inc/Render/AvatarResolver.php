<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Service\AvatarCrop;
use GHOSTLABS\UNDERCARD\Service\AvatarProbeInterface;
use GHOSTLABS\UNDERCARD\Service\GravatarProbe;

final class AvatarResolver
{
    public function __construct(
        private readonly AvatarProbeInterface $probe = new GravatarProbe(),
        private readonly AvatarCrop $crop = new AvatarCrop(),
    ) {
    }

    public function resolve(Author $author, int $size = Author::AVATAR_SIZE): ResolvedAvatar
    {
        $photo = $author->photoUrl($size);

        if ($photo !== '') {

            $cropped = $size === Author::AVATAR_SIZE ? $this->crop->url($author->photoId()) : '';

            return new ResolvedAvatar(AvatarSource::Photo, $cropped !== '' ? $cropped : $photo);
        }

        if ($this->probe->exists($author)) {
            $gravatar = $author->gravatarUrl($size);

            if ($gravatar !== '') {
                return new ResolvedAvatar(AvatarSource::Gravatar, $gravatar);
            }
        }

        $initials = $author->initials();

        if ($initials !== '') {
            return new ResolvedAvatar(AvatarSource::Initials, $initials);
        }

        return new ResolvedAvatar(AvatarSource::Icon);
    }
}
