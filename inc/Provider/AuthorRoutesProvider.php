<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\RestRoute\AuthorRoutes;
use GHOSTLABS\UNDERCARD\Service\Loader;

final class AuthorRoutesProvider implements FeatureProviderInterface
{
    public function register(Loader $loader): void
    {
        $loader->add_action('rest_api_init', $this, 'routes');
    }

    public function routes(): void
    {
        (new AuthorRoutes())->routes();
    }
}
