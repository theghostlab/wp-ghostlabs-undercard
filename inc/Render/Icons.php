<?php

namespace GHOSTLABS\UNDERCARD\Render;

final class Icons
{
    
    public static function person(string $class = ''): string
    {
        return self::svg(
            '<path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.34 0-10 1.67-10 5v2a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-2c0-3.33-6.66-5-10-5Z"/>',
            $class,
        );
    }

    public static function download(string $class = ''): string
    {
        return self::svg(
            '<path d="M12 3a1 1 0 0 1 1 1v8.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.42l2.3 2.3V4a1 1 0 0 1 1-1Z"/>'
            . '<path d="M4 16a1 1 0 0 1 1 1v2h14v-2a1 1 0 1 1 2 0v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1Z"/>',
            $class,
        );
    }

    public static function qrCode(string $class = ''): string
    {
        return self::svg(
            '<path d="M3 3h8v8H3V3Zm2 2v4h4V5H5Zm8-2h8v8h-8V3Zm2 2v4h4V5h-4ZM3 13h8v8H3v-8Zm2 2v4h4v-4H5Z"/>'
            . '<path d="M13 13h3v3h-3v-3Zm5 0h3v3h-3v-3Zm-5 5h3v3h-3v-3Zm5 0h3v3h-3v-3Z"/>',
            $class,
        );
    }

    public static function close(string $class = ''): string
    {
        return self::svg(
            '<path d="M6.4 5 5 6.4 10.6 12 5 17.6 6.4 19l5.6-5.6 5.6 5.6 1.4-1.4-5.6-5.6L19 6.4 17.6 5 12 10.6 6.4 5Z"/>',
            $class,
        );
    }

    private static function svg(string $paths, string $class): string
    {
        $attributes = 'viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"';

        if ($class !== '') {
            $attributes = 'class="' . esc_attr($class) . '" ' . $attributes;
        }

        return '<svg ' . $attributes . '>' . $paths . '</svg>';
    }
}
