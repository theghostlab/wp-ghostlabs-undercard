<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Service\BylineEditor;
use GHOSTLABS\UNDERCARD\Service\BylineField;
use GHOSTLABS\UNDERCARD\Service\CoreByline;
use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\PostAuthors;

final class BylineProvider implements FeatureProviderInterface
{
    public function __construct(
        private readonly PostAuthors $postAuthors,
    ) {
    }

    public function register(Loader $loader): void
    {
        $field  = new BylineField($this->postAuthors);
        $editor = new BylineEditor($field);

        $loader->add_action('rest_api_init', $field, 'registerField');
        $loader->add_action('enqueue_block_editor_assets', $editor, 'enqueue');

        (new CoreByline($this->postAuthors))->register();
    }
}
