<?php

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;

class BlockFactory
{
    
    private const NAME_PREFIX = 'ghostlabs/undercard-';

    private string $blocksDir = GHOSTLABS_UNDERCARD_PLUGIN_PATH.'build/blocks';

    private array $dirBlackList = ['..', '.', '.DS_Store'];

    public function setRegisterBlocks(): array
    {
        if( !is_dir($this->blocksDir) ) return [];

        $scanResult = scandir($this->blocksDir);
        if ($scanResult === false) return [];

        $filteredBlocks = array_diff($scanResult, $this->dirBlackList);
        $blocks = array_values($filteredBlocks);
    
        if( empty($blocks) ) return [];

        $new_functions = [];

        foreach ($blocks as $block)
        {
            $new_functions[] = function () use ($block) {
                $blockType = $this->blocksDir.'/'.$block;
                register_block_type( $blockType );
            };
        }

        return $new_functions;
    }

    public function versionAssets(array $metadata): array
    {
        $name = $metadata['name'] ?? '';

        if (! is_string($name) || ! str_starts_with($name, self::NAME_PREFIX) || isset($metadata['version'])) {
            return $metadata;
        }

        $file = $metadata['file'] ?? null;

        $stamps = is_string($file)
            ? array_map(static fn (string $css): int => (int) filemtime($css), glob(dirname($file) . '/*.css') ?: [])
            : [];

        $metadata['version'] = $stamps === []
            ? PluginConfig::version()
            : PluginConfig::version() . '-' . max($stamps);

        return $metadata;
    }
}