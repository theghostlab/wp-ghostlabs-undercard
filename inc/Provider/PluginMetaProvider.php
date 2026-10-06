<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\PluginMeta;

final class PluginMetaProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly PluginMeta $meta = new PluginMeta(),
    ) {
    }

    public function register(Loader $loader): void
    {
        add_filter('plugin_action_links_' . PluginMeta::basename(), [ $this->meta, 'actionLinks' ]);

        add_filter('plugin_row_meta', [ $this->meta, 'rowMeta' ], 10, 2);
    }
}
