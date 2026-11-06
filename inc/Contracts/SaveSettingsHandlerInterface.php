<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface SaveSettingsHandlerInterface
{
    
    public function save(string $data): ?array;
}
