<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;

interface FeatureProviderInterface
{
    
    public function register(Loader $loader): void;
}
