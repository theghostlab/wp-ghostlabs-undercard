<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Contracts\PluginIdentityInterface;

class PluginIdentity implements PluginIdentityInterface
{
    public function getPluginFile(): string
    {
        return GHOSTLABS_UNDERCARD_PLUGIN_SLUG;
    }

    public function getUpdaterSlug(): string
    {
        return PluginConfig::updaterSlug();
    }

    public function getVersion(): string
    {
        return PluginConfig::version();
    }

    public function getRestNamespace(): string
    {
        return PluginConfig::restNameSpace();
    }

    public function getDomain(): string
    {
        return PluginConfig::domain();
    }

    public function getSettingsMenuSlug(): string
    {
        return PluginConfig::$settingsMenuSlug;
    }
}
