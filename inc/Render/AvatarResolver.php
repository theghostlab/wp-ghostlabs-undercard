<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Enum\AvatarSource;
use GHOSTLABS\UNDERCARD\Service\AvatarProbeInterface;
use GHOSTLABS\UNDERCARD\Service\GravatarProbe;

final class AvatarResolver
{
    public function __construct(
        private readonly AvatarProbeInterface $probe = new GravatarProbe(),
    ) {
    }

    public function resolve(Author $author, int $size = Author::AVATAR_SIZE): ResolvedAvatar
    {
        $photo = $author->photoUrl($size);

        if ($photo !== '') {
            return new ResolvedAvatar(AvatarSource::Photo, $photo);
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
