<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Boot;

use GHOSTLABS\UNDERCARD\FreeKernel;
use GHOSTLABS\UNDERCARD\Kernel;

final class Edition
{
    
    public static function libraries(): array
    {
        return [
            GHOSTLABS_UNDERCARD_PLUGIN_PATH . 'lib/wp-kit/wp-kit.php',
        ];
    }

    public static function kernel(): string
    {
        return FreeKernel::class;
    }
}
