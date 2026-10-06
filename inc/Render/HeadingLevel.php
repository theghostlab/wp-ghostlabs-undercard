<?php

namespace GHOSTLABS\UNDERCARD\Render;

final class HeadingLevel
{
    
    public const LEVELS = [2, 3, 4, 5, 6];

    public const DEFAULT_LEVEL = 2;

    public static function tag(array $attributes): string
    {
        return 'h' . self::level($attributes);
    }

    public static function level(array $attributes): int
    {
        
        $raw = $attributes['headingLevel'] ?? null;

        if (! is_int($raw) && ! (is_string($raw) && preg_match('/^[0-9]$/', $raw) === 1)) {
            return self::DEFAULT_LEVEL;
        }

        $level = (int) $raw;

        return in_array($level, self::LEVELS, true) ? $level : self::DEFAULT_LEVEL;
    }
}
