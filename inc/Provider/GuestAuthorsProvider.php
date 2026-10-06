<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\GuestAuthorFields;
use GHOSTLABS\UNDERCARD\Service\GuestAuthors;
use GHOSTLABS\UNDERCARD\Service\Loader;

final class GuestAuthorsProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly GuestAuthors $guests = new GuestAuthors(),
        private readonly GuestAuthorFields $fields = new GuestAuthorFields(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->guests->register();
        $this->fields->register();
    }
}
