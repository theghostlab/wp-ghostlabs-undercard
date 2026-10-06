<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

class SaveGuestSlug extends SaveSetting
{
    protected function key(): string
    {
        return SiteSettings::GUEST_SLUG;
    }

    protected function sanitize(string $data): string
    {
        $slug = GuestBase::normalize($data);

        if (GuestBase::owner($slug) === null) {
            return $slug;
        }

        $current = (new OptionsService())->get(SiteSettings::GUEST_SLUG);

        return is_string($current) && $current !== '' && GuestBase::owner($current) === null
            ? $current
            : SiteSettings::DEFAULT_GUEST_SLUG;
    }
}
