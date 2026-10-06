<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

final class Upsell
{
    
    public static function urlFor(UpsellSource $source): string
    {
        
        return (string) apply_filters('ghostlabs_undercard_upgrade_url', '', $source);
    }

    public static function isDismissed(UpsellSource $source): bool
    {
        
        return (bool) apply_filters('ghostlabs_undercard_upsell_dismissed', false, $source);
    }

    public static function dismissalKeyFor(UpsellSource $source): string
    {
        
        return (string) apply_filters('ghostlabs_undercard_upsell_dismissal_key', '', $source);
    }
}
