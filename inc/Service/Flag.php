<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

final class Flag
{
    public static function normalize(string $data): string
    {
        return $data === '1' ? '1' : '';
    }
}
