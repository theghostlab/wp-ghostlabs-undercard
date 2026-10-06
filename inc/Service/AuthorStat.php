<?php

namespace GHOSTLABS\UNDERCARD\Service;

final class AuthorStat
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $value,
    ) {
    }
}
