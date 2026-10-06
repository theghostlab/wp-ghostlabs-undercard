<?php

namespace GHOSTLABS\UNDERCARD\Render;

use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Entity\Credit;
use GHOSTLABS\UNDERCARD\Enum\AuthorRole;

final class Byline
{
    
    public static function render(array $credits, callable $card): string
    {
        $rendered = [];

        foreach ($credits as $credit) {
            $markup = $card($credit->author);

            if ($markup !== '') {
                $rendered[] = [ $credit, $markup ];
            }
        }

        if ($rendered === []) {
            return '';
        }

        if (count($rendered) === 1 && $rendered[0][0]->role === AuthorRole::Author) {
            return $rendered[0][1];
        }

        $items = '';

        foreach ($rendered as [ $credit, $markup ]) {

            $items .= '<li class="ghostlabs-u-byline__credit">'
                . '<p class="ghostlabs-u-byline__role">' . esc_html($credit->role->label()) . '</p>'
                . $markup
                . '</li>';
        }

        return '<ul class="ghostlabs-u-byline">' . $items . '</ul>';
    }
}
