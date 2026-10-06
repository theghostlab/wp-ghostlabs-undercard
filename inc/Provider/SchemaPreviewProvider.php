<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\SchemaPreviewPanel;

final class SchemaPreviewProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly SchemaPreviewPanel $panel = new SchemaPreviewPanel(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->panel->register();
    }
}
