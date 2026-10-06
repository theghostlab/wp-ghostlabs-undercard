<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\SiteSettings;

final class SiteSettingsProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly SiteSettings $settings = new SiteSettings(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->settings->register($loader);
    }
}
