<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;

interface AvatarProbeInterface
{
    public function exists(Author $author): bool;

    public function forget(Author $author): void;
}
