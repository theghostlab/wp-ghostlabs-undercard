<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Config\PluginConfig;
use GhostLabs\WPKit\Contracts\MutableOptionsServiceInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

class OptionsService implements MutableOptionsServiceInterface
{
    public function delete(string $key): ?bool {
        $options = get_option(PluginConfig::optionKey(), []);

        if ( !is_array($options) || !array_key_exists($key, $options) ) {
            return null;
        }

        unset($options[$key]);

        return update_option(PluginConfig::optionKey(), $options);
    }

    public function get(string $key): mixed {
        $options = get_option(PluginConfig::optionKey(), []);

        if ( !is_array($options) ) {
            return null;
        }

        return $options[$key] ?? null;
    }

    public function save(string $key, mixed $data): bool
    {
        $options = get_option(PluginConfig::optionKey(), []);

        if ( !is_array($options) ) {
            $options = [];
        }

        $options[$key] = $data;

        return update_option(PluginConfig::optionKey(), $options);
    }
}
