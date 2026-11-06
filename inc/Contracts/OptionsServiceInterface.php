<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface OptionsServiceInterface
{
    
    public function get(string $key): mixed;
}
