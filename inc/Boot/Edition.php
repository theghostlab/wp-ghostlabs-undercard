<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Boot;

use GHOSTLABS\UNDERCARD\FreeKernel;
use GHOSTLABS\UNDERCARD\Kernel;

final class Edition
{
    
    public static function libraries(): array
    {
        return [];
    }

    public static function kernel(): string
    {
        return FreeKernel::class;
    }
}
