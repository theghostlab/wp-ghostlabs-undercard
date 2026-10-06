<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;

final class Geometry
{
    
    private const VERTICAL = [
        'meridian' => ['width' => 360, 'height' => 520, 'pitch' => 34, 'floor' => null],
        'cobalt'   => ['width' => 360, 'height' => 520, 'pitch' => 50, 'floor' => null],
        'aurora'   => ['width' => 380, 'height' => 520, 'pitch' => null, 'floor' => null],
        'nfc'      => ['width' => 390, 'height' => 680, 'pitch' => null, 'floor' => null],
    ];

    private const HORIZONTAL = [
        'meridian' => ['width' => 600, 'height' => 340, 'pitch' => 38, 'floor' => 340],
        'cobalt'   => ['width' => 560, 'height' => 320, 'pitch' => 42, 'floor' => null],
        'aurora'   => ['width' => 600, 'height' => 340, 'pitch' => null, 'floor' => null],
        'nfc'      => ['width' => 640, 'height' => 360, 'pitch' => null, 'floor' => null],
    ];

    private const COMPACT = [
        'row'  => ['width' => 480, 'height' => 88],
        'chip' => ['width' => 240, 'height' => 64],
    ];

    private const MINI = [
        'aurora' => ['width' => 280, 'height' => 170],
    ];

    private const DRAWN_ROWS = 3;

    private const MIN_HEIGHT = 64;

    public static function ratio(
        CardFamily $family,
        CardLayout $layout,
        int $rows,
        bool $fixedHeight = false,
    ): string {
        if ($layout->isCompact()) {
            $size = self::COMPACT[$layout === CardLayout::Row ? 'row' : 'chip'];

            return $size['width'] . ' / ' . $size['height'];
        }

        if ($layout === CardLayout::Mini && isset(self::MINI[$family->value])) {
            $mini = self::MINI[$family->value];

            return $mini['width'] . ' / ' . $mini['height'];
        }

        $table = $layout === CardLayout::Horizontal ? self::HORIZONTAL : self::VERTICAL;
        $size  = $table[$family->value];

        return $size['width'] . ' / ' . self::height($size, $rows, $fixedHeight);
    }

    public static function collapsedRatio(CardFamily $family, CardLayout $layout): ?string
    {
        return $layout === CardLayout::Row
            ? self::ratio($family, CardLayout::Chip, 0)
            : null;
    }

    private static function height(array $size, int $rows, bool $fixedHeight): int
    {
        if ($size['pitch'] === null || $fixedHeight) {
            return $size['height'];
        }

        $height = $size['height'] + ((max(0, $rows) - self::DRAWN_ROWS) * $size['pitch']);

        return max($size['floor'] ?? self::MIN_HEIGHT, $height);
    }
}
