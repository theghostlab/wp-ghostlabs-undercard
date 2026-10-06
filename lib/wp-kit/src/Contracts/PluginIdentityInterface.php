<?php

namespace GhostLabs\WPKit\Contracts;

interface PluginIdentityInterface
{
    
    public function getPluginFile(): string;

    public function getUpdaterSlug(): string;

    public function getVersion(): string;

    public function getRestNamespace(): string;

    public function getDomain(): string;

    public function getSettingsMenuSlug(): string;
}
