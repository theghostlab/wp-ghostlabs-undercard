<?php

namespace GHOSTLABS\UNDERCARD\Config;

final class PluginConfig
{
    public static string $domain = 'ghostlabs';
    public static string $slug = 'undercard';
    private static string $restVersion = 'v1';
    public static string $settingsMenuSlug = 'ghostlabs-undercard-settings';

    static public function version(): string {
        return defined('GHOSTLABS_UNDERCARD_VERSION')
            ? (string) constant('GHOSTLABS_UNDERCARD_VERSION')
            : '0.0.0';
    }

    static public function tier(): string {
        return defined('GHOSTLABS_UNDERCARD_EDITION')
            ? (string) constant('GHOSTLABS_UNDERCARD_EDITION')
            : 'free';
    }

    static public function getAppDomain(): string {
        return sprintf('%s-%s', self::$domain, self::$slug);
    }

    static public function domain(): string {
        return self::getAppDomain();
    }

    static public function optionKey(): string {
        return sprintf('%s_%s_options', self::$domain, self::$slug);
    }

    static public function upgradePendingTransient(): string {
        return sprintf('_%s_%s_upgrade_pending', self::$domain, self::$slug);
    }

    static private function getPluginSlug() : string {

        [ , $t2 ] = explode( '/', GHOSTLABS_UNDERCARD_PLUGIN_SLUG );

        return str_replace( '.php', '', $t2 );
    }

    public static function restNameSpace(): string {
        return sprintf("%s/%s/%s/%s", self::$domain, self::$slug, self::tier(), self::$restVersion);
    }

    static public function updaterSlug(): string {
        return self::getPluginSlug();
    }
}
