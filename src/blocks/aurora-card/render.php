<?php


use GHOSTLABS\UNDERCARD\Entity\Author;
use GHOSTLABS\UNDERCARD\Render\Byline;
use GHOSTLABS\UNDERCARD\Render\CardAuthor;
use GHOSTLABS\UNDERCARD\Render\AuroraCard;
use GHOSTLABS\UNDERCARD\Service\PostAuthors;

defined('ABSPATH') || exit;

$ghostlabs_undercard_credits = ( new CardAuthor( new PostAuthors( $GLOBALS['wpdb'] ) ) )->creditsForBlock(
    $attributes,
    (int) ( $block->context['postId'] ?? 0 ),
);

if ( $ghostlabs_undercard_credits === [] ) {
    return;
}

$ghostlabs_undercard_wrapper = get_block_wrapper_attributes();
$ghostlabs_undercard_renderer = new AuroraCard();
$ghostlabs_undercard_card     = Byline::render(
    $ghostlabs_undercard_credits,
    static fn( Author $ghostlabs_undercard_person ): string => $ghostlabs_undercard_renderer->render( $ghostlabs_undercard_person, $attributes ),
);

printf('<div %s>%s</div>', $ghostlabs_undercard_wrapper, $ghostlabs_undercard_card); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
