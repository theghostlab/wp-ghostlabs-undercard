<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GHOSTLABS\UNDERCARD\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpgradeEndpoints
{
    
    private const MARKETING_BASE = 'https://theghostlab.io';

    private const MARKETING_PATH = '/undercard';

    private const DOCS_PATH = '/docs/undercard';

    private const SUPPORT_BASE = 'https://wordpress.org/support/plugin/';

    public static function marketingUrl(string $path): string
    {
        return self::MARKETING_BASE . $path;
    }

    public static function docsUrl(): string
    {
        return self::marketingUrl(self::DOCS_PATH);
    }

    public static function supportUrl(): string
    {
        return self::SUPPORT_BASE . PluginConfig::getAppDomain() . '/';
    }

    public static function upgradeUrl(UpsellSource $source): string
    {

        return self::marketingUrl(self::MARKETING_PATH) . '?from=' . $source->value;
    }

}
