<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\RestRoute\SettingsRoutes;
use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\Settings;
use GHOSTLABS\UNDERCARD\Contracts\PluginIdentityInterface;

final class AdminResourcesProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly PluginIdentityInterface $identity,
        private readonly Settings $settings = new Settings(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $loader->add_action('admin_menu', $this->settings, 'page');
        $loader->add_action('admin_enqueue_scripts', $this->settings, 'scripts');
        $loader->add_action('rest_api_init', $this, 'routes');
    }

    public function routes(): void
    {
        (new SettingsRoutes($this->identity))->routes();
    }
}
