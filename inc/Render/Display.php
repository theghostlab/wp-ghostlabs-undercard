<?php

namespace GHOSTLABS\UNDERCARD\Render;

final class Display
{
    
    public static function url(string $url): string
    {
        $shown = preg_replace('#^https?://(?:www\.)?#i', '', $url);

        return rtrim($shown ?? $url, '/');
    }
}
