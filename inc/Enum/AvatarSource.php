<?php

namespace GHOSTLABS\UNDERCARD\Enum;

enum AvatarSource
{
    
    case Photo;

    case Gravatar;

    case Initials;

    case Icon;

    public function isImage(): bool
    {
        return $this === self::Photo || $this === self::Gravatar;
    }
}
