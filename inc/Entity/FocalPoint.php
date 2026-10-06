<?php

namespace GHOSTLABS\UNDERCARD\Entity;

final class FocalPoint
{
    private function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {
    }

    public static function fromAttribute(mixed $value): ?self
    {
        if (! is_array($value) || ! isset($value['x'], $value['y'])) {
            return null;
        }

        if (! is_numeric($value['x']) || ! is_numeric($value['y'])) {
            return null;
        }

        return new self(
            self::clamp((float) $value['x']),
            self::clamp((float) $value['y']),
        );
    }

    public static function scale(mixed $value): float
    {
        if (! is_numeric($value)) {
            return 1.0;
        }

        return max(0.5, min(4.0, (float) $value));
    }

    public function toObjectPosition(): string
    {
        return self::percent($this->x) . ' ' . self::percent($this->y);
    }

    private static function clamp(float $n): float
    {
        return max(0.0, min(1.0, $n));
    }

    private static function percent(float $n): string
    {
        return self::trim($n * 100) . '%';
    }

    private static function trim(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.') ?: '0';
    }
}
