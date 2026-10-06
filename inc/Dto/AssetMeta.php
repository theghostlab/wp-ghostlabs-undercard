<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Dto;

if ( ! defined( 'ABSPATH' ) ) exit;

final class AssetMeta
{
    
    private function __construct(
        public readonly array $dependencies,
        public readonly string $version,
    ) {
    }

    public static function read(string $file, string $fallbackVersion): self
    {
        $meta = is_readable($file) ? require $file : null;

        if (! is_array($meta)) {
            return new self([], $fallbackVersion);
        }

        $dependencies = [];

        foreach (is_array($meta['dependencies'] ?? null) ? $meta['dependencies'] : [] as $dependency) {
            if (is_string($dependency)) {
                $dependencies[] = $dependency;
            }
        }

        $version = $meta['version'] ?? null;

        return new self($dependencies, is_string($version) ? $version : $fallbackVersion);
    }
}
