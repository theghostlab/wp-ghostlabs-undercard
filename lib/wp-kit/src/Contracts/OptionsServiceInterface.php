<?php

namespace GhostLabs\WPKit\Contracts;

interface OptionsServiceInterface
{
    
    public function get(string $key): mixed;
}
