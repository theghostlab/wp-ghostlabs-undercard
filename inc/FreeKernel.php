<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD;

use GHOSTLABS\UNDERCARD\Provider\FeatureProviderInterface;
use GHOSTLABS\UNDERCARD\Provider\UpsellProvider;

if ( ! defined( 'ABSPATH' ) ) exit;

final class FreeKernel extends Kernel
{
    
    protected function providers(): array
    {
        return [
            ...parent::providers(),
            new UpsellProvider(),
        ];
    }
}
