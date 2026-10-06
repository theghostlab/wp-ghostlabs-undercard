<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

class SaveBylineFollowsCore extends SaveSetting
{
    protected function key(): string
    {
        return SiteSettings::BYLINE_FOLLOWS_CORE;
    }

    protected function sanitize(string $data): string
    {
        return Flag::normalize($data);
    }
}
