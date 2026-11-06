<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Dto\Validation;

use GHOSTLABS\UNDERCARD\Contracts\DTOValidationInterface;

class SettingsValidation implements DTOValidationInterface
{
    
    public static function rules(): array
    {
        return [

            'bylinePostTypes' => [
                'regex' => '/^[a-z0-9_,-]*$/',
                'max'   => 512,
            ],

            'guestSlug' => [ 'max' => 64 ],

            'bylineFollowsCore' => [ 'enum' => [ '', '1' ] ],
            'vcardEnabled'      => [ 'enum' => [ '', '1' ] ],
        ];
    }
}
