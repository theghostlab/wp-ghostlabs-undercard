<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface MutableOptionsServiceInterface extends OptionsServiceInterface
{
    
    public function save(string $key, mixed $data): bool;

    public function delete(string $key): ?bool;
}
