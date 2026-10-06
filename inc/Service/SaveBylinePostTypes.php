<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

class SaveBylinePostTypes extends SaveSetting
{
    protected function key(): string
    {
        return SiteSettings::BYLINE_POST_TYPES;
    }

    protected function sanitize(string $data): string
    {
        $submitted = array_filter(array_map('trim', explode(',', $data)));

        $eligible = BylineField::eligiblePostTypes();

        return implode(',', array_values(array_filter(
            $submitted,
            static fn (string $type): bool => in_array($type, $eligible, true)
        )));
    }
}
