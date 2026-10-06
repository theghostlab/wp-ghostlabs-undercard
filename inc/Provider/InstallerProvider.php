<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Installer;
use GHOSTLABS\UNDERCARD\Service\Loader;

final class InstallerProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly Installer $installer,
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->installer->register();
    }
}
