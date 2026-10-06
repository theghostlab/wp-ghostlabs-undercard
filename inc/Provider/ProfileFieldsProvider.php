<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\UserProfile;

final class ProfileFieldsProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly UserProfile $profile = new UserProfile(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->profile->register();
    }
}
