<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Enum\AvatarSource;

final class ResolvedAvatar
{
    public function __construct(
        public readonly AvatarSource $source,
        
        public readonly string $value = '',
    ) {
    }

    public function isImage(): bool
    {
        return $this->source->isImage();
    }
}
