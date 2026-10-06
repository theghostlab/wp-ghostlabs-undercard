<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Config;

use GHOSTLABS\UNDERCARD\Enum\AuthorRole;
use GHOSTLABS\UNDERCARD\Enum\CardFamily;
use GHOSTLABS\UNDERCARD\Enum\CardLayout;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TierMatrix
{
    
    public const CREDENTIAL_PROPERTIES = [ 'jobTitle', 'worksFor', 'alumniOf', 'knowsAbout' ];

    public static function paidEditionName(): string
    {
        return __('Undercard Pro', 'ghostlabs-undercard');
    }

    public static function paidEditionBadge(): string
    {
        return __('Pro', 'ghostlabs-undercard');
    }

    public static function capabilities(): array
    {
        return [
            [
                'key'   => 'cards',
                'label' => __('Card designs and layouts', 'ghostlabs-undercard'),

                'free'  => self::cardQuantity(),
                'paid'  => self::cardQuantity(),
            ],
            [
                'key'   => 'guests',
                'label' => __('Guest authors, with a profile URL of their own', 'ghostlabs-undercard'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'co-authors',
                'label' => __('Co-authors, in the order you choose', 'ghostlabs-undercard'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'roles',
                'label' => __('Credit roles', 'ghostlabs-undercard'),
                'free'  => self::roleNamesAsProse(),
                'paid'  => self::roleNamesAsProse(),
            ],
            [
                'key'   => 'identity-fields',
                'label' => __('Author identity fields, shown on the card', 'ghostlabs-undercard'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'contact-download',
                'label' => __('Contact download', 'ghostlabs-undercard'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'preview',
                'label' => __('Structured data preview', 'ghostlabs-undercard'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'structured-data',
                'label' => __('Structured data published as JSON-LD', 'ghostlabs-undercard'),
                'free'  => false,
                'paid'  => true,
            ],
            [

                'key'   => 'credentials',
                'label' => __('Credentials in the structured data', 'ghostlabs-undercard'),
                'free'  => __('Shown on the card', 'ghostlabs-undercard'),
                'paid'  => implode(', ', self::CREDENTIAL_PROPERTIES),
            ],
            [
                'key'   => 'host-seo',
                'label' => __('Yoast and Rank Math integration', 'ghostlabs-undercard'),
                'free'  => false,
                'paid'  => true,
            ],
            [
                'key'   => 'author-pages',
                'label' => __('Author profile pages', 'ghostlabs-undercard'),
                'free'  => false,
                'paid'  => true,
            ],
            [
                'key'   => 'archive-byline',
                'label' => __('Author archives that follow the assigned byline', 'ghostlabs-undercard'),
                'free'  => false,
                'paid'  => true,
            ],
        ];
    }

    public static function paidOnlyLabels(): array
    {
        return array_values(array_map(
            static fn (array $capability): string => $capability['label'],
            array_filter(
                self::capabilities(),
                static fn (array $capability): bool => $capability['free'] === false
            )
        ));
    }

    public static function freeLabels(): array
    {
        return array_values(array_map(
            static fn (array $capability): string => $capability['label'],
            array_filter(
                self::capabilities(),
                static fn (array $capability): bool => $capability['free'] !== false
            )
        ));
    }

    private static function cardQuantity(): string
    {
        return sprintf(
            /* translators: 1: how many card designs ship, e.g. 4. 2: how many layouts, e.g. 5. */
            __('All %1$d designs, %2$d layouts', 'ghostlabs-undercard'),
            count(CardFamily::cases()),
            count(CardLayout::cases())
        );
    }

    private static function roleNamesAsProse(): string
    {
        return implode(', ', array_map(
            static fn (AuthorRole $role): string => $role->label(),
            AuthorRole::cases()
        ));
    }
}
