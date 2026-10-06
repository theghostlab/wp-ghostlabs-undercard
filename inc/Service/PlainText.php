<?php

namespace GHOSTLABS\UNDERCARD\Service;

final class PlainText
{
    public static function from(string $value): string
    {
        return trim(html_entity_decode(
            wp_strip_all_tags($value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        ));
    }
}
