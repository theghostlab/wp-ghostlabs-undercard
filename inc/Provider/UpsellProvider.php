<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Provider;

use GHOSTLABS\UNDERCARD\Enum\UpsellSource;
use GHOSTLABS\UNDERCARD\Service\Loader;
use GHOSTLABS\UNDERCARD\Service\UpgradeEndpoints;
use GHOSTLABS\UNDERCARD\Service\UpsellLinks;
use GHOSTLABS\UNDERCARD\Service\UpsellNoticeStyle;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpsellProvider implements FeatureProviderInterface
{
    
    private const DISMISSIBLE = [ UpsellSource::BylineSecondCredit ];

    public function __construct(
        private readonly UpsellLinks $links = new UpsellLinks(),
        private readonly UpsellNoticeStyle $noticeStyle = new UpsellNoticeStyle(),
    ) {
    }

    public function register(Loader $loader): void
    {
        $this->links->register();

        $this->noticeStyle->register();

        add_filter('ghostlabs_undercard_upgrade_url', [ $this, 'upgradeUrl' ], 10, 2);
        add_filter('ghostlabs_undercard_upsell_dismissed', [ $this, 'isDismissed' ], 10, 2);
        add_filter('ghostlabs_undercard_upsell_dismissal_key', [ $this, 'dismissalKey' ], 10, 2);

        add_action('init', [ $this, 'registerDismissalMeta' ]);
    }

    public function upgradeUrl(string $url, UpsellSource $source): string
    {
        return UpgradeEndpoints::upgradeUrl($source);
    }

    public function dismissalKey(string $key, UpsellSource $source): string
    {
        return in_array($source, self::DISMISSIBLE, true)
            ? self::metaKeyFor($source)
            : '';
    }

    public function isDismissed(bool $dismissed, UpsellSource $source): bool
    {
        $key = self::metaKeyFor($source);

        if (! in_array($source, self::DISMISSIBLE, true)) {
            return false;
        }

        return (bool) get_user_meta(get_current_user_id(), $key, true);
    }

    public function registerDismissalMeta(): void
    {
        foreach (self::DISMISSIBLE as $source) {
            register_meta('user', self::metaKeyFor($source), [
                'type'         => 'boolean',
                'single'       => true,
                'default'      => false,

                'show_in_rest' => [
                    'schema' => [
                        'type'    => 'boolean',
                        'context' => [ 'edit' ],
                    ],
                ],
                'auth_callback' => static fn (
                    bool $allowed,
                    string $metaKey,
                    int $objectId
                ): bool => current_user_can('edit_user', $objectId),
            ]);
        }
    }

    private static function metaKeyFor(UpsellSource $source): string
    {
        return 'ghostlabs_undercard_dismissed_' . str_replace('-', '_', $source->value);
    }
}
