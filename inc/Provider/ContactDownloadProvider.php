<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\VCardDownload;

final class ContactDownloadProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly VCardDownload $vcard = new VCardDownload(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->vcard->register();
    }
}
