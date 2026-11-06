<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\RestRoute;

use GHOSTLABS\UNDERCARD\Dto\Input\SettingsDto;
use GHOSTLABS\UNDERCARD\Dto\Validation\SettingsValidation;
use GHOSTLABS\UNDERCARD\Service\SaveBylineFollowsCore;
use GHOSTLABS\UNDERCARD\Service\SaveBylinePostTypes;
use GHOSTLABS\UNDERCARD\Service\SaveGuestSlug;
use GHOSTLABS\UNDERCARD\Service\SaveVCardEnabled;

class SettingsRoutes extends AbstractSettingsRoutes
{
    
    use GuardsGuestBase;

    protected function dtoMap(): array
    {
        return [
            'settings' => [ SettingsDto::class, SettingsValidation::rules() ],
        ];
    }

    protected function handlerMap(): array
    {
        return [
            'bylinePostTypes'   => SaveBylinePostTypes::class,
            'guestSlug'         => SaveGuestSlug::class,
            'bylineFollowsCore' => SaveBylineFollowsCore::class,
            'vcardEnabled'      => SaveVCardEnabled::class,
        ];
    }
}
