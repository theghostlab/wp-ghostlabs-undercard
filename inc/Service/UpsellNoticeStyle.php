<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Dto\AssetMeta;

if ( ! defined( 'ABSPATH' ) ) exit;

final class UpsellNoticeStyle
{
    private const HANDLE = 'ghostlabs-undercard-upsell-notice';

    private const ASSET = 'build/admin/upsell-notice';

    private const PROFILE_SCREENS = [ 'profile.php', 'user-edit.php' ];

    private const EDITOR_SCREENS = [ 'post.php', 'post-new.php' ];

    public function register(): void
    {
        add_action('admin_enqueue_scripts', [ $this, 'enqueue' ]);
    }

    public function enqueue(string $hook): void
    {
        if (! $this->isPanelScreen($hook)) {
            return;
        }

        wp_enqueue_style(
            self::HANDLE,
            GHOSTLABS_UNDERCARD_PLUGIN_URL . 'build/admin/style-upsell-notice.css',
            [],
            AssetMeta::read(GHOSTLABS_UNDERCARD_PLUGIN_PATH . self::ASSET . '.asset.php', GHOSTLABS_UNDERCARD_VERSION)->version
        );
    }

    private function isPanelScreen(string $hook): bool
    {
        if (in_array($hook, self::PROFILE_SCREENS, true)) {
            return true;
        }

        if (! in_array($hook, self::EDITOR_SCREENS, true)) {
            return false;
        }

        $screen = get_current_screen();

        return $screen !== null && $screen->post_type === GuestAuthors::POST_TYPE;
    }
}
