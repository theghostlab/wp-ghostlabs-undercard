<?php

namespace GhostLabs\WPKit\Contracts;

interface SaveSettingsHandlerInterface
{
    
    public function save(string $data): ?array;
}
