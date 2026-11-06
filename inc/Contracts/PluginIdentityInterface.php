<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Contracts;

if ( ! defined( 'ABSPATH' ) ) exit;

interface PluginIdentityInterface
{
    
    public function getPluginFile(): string;

    public function getUpdaterSlug(): string;

    public function getVersion(): string;

    public function getRestNamespace(): string;

    public function getDomain(): string;

    public function getSettingsMenuSlug(): string;
}
