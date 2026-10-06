<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\RestRoute\UpgradeRoutes;
use GHOSTLABS\UNDERCARD\Service\Loader;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The in-place upgrade's two REST routes, behind the strictest gate in the
 * plugin.
 *
 * ⚠️ Hooked on `rest_api_init` **directly**, never deferred through the
 * `Loader`: the routes must be reachable whatever else the kernel wires, since
 * installing a build that works is what they are for.
 *
 * Construction is deferred into the hook, so an admin page load never builds
 * the upgrade stack — only a REST request does.
 */
final class UpgradeRoutesProvider implements FeatureProviderInterface
{
    public function register(Loader $loader): void
    {
        add_action('rest_api_init', [ $this, 'routes' ]);
    }

    public function routes(): void
    {
        (new UpgradeRoutes())->routes();
    }
}
