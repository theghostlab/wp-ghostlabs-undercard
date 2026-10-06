<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use Exception;
use GHOSTLABS\UNDERCARD\Service\BlockFactory;
use GHOSTLABS\UNDERCARD\Service\Loader;

final class BlocksProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly BlockFactory $blocks = new BlockFactory(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $blocks = $this->blocks->setRegisterBlocks();

        if (empty($blocks)) {
            return;
        }

        add_filter('block_type_metadata', [$this->blocks, 'versionAssets']);

        foreach ($blocks as $block) {
            add_action('init', $block);
        }
    }
}
