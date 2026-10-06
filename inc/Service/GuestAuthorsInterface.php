<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;

interface GuestAuthorsInterface
{
    
    public function bySlug(string $slug): ?Author;
}
