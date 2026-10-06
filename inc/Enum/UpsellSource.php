<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Enum;

if ( ! defined( 'ABSPATH' ) ) exit;

enum UpsellSource: string
{
    
    case ProfilePreview = 'profile-preview';

    case PluginsRow = 'plugins-row';

    case Settings = 'settings';

    case BylineSecondCredit = 'byline-second-credit';

    case SettingsTable = 'settings-table';
}
