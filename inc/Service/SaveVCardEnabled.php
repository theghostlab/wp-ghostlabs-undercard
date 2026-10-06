<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

class SaveVCardEnabled extends SaveSetting
{
    protected function key(): string
    {
        return SiteSettings::VCARD_ENABLED;
    }

    protected function sanitize(string $data): string
    {
        return Flag::normalize($data);
    }
}
