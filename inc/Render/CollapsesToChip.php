<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;

trait CollapsesToChip
{
    
    private static function collapsedRatioProperty(CardFamily $family, CardLayout $layout): string
    {
        $narrow = Geometry::collapsedRatio($family, $layout);

        return $narrow === null
            ? ''
            : '--ghostlabs-u-ratio-narrow:' . esc_attr($narrow) . ';';
    }
}
