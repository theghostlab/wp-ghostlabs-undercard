<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD;

use GHOSTLABS\UNDERCARD\Provider\FeatureProviderInterface;
use GHOSTLABS\UNDERCARD\Provider\UpgradeRoutesProvider;
use GHOSTLABS\UNDERCARD\Provider\UpsellProvider;
use GHOSTLABS\UNDERCARD\Service\Upgrade\BackendEndpoints;

if ( ! defined( 'ABSPATH' ) ) exit;

final class FreeKernel extends Kernel
{
    protected function bootCollaborators(): void
    {
        parent::bootCollaborators();

        $this->relaxTransportSecurityInDevelopment();
    }

    protected function providers(): array
    {
        return [
            ...parent::providers(),
            new UpsellProvider(),

            new UpgradeRoutesProvider(),
        ];
    }

    private function relaxTransportSecurityInDevelopment(): void
    {
    }

}
