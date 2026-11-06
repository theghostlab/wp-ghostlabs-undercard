<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service;

use GHOSTLABS\UNDERCARD\Entity\Author;

class AvatarCrop
{
    public const SIZE_NAME = 'ghostlabs-undercard-avatar';

    public const SIZE = Author::AVATAR_SIZE;

    public function url(int $attachmentId): string
    {
        if ($attachmentId <= 0) {
            return '';
        }

        if ($this->ensure($attachmentId)) {
            return $this->sizeUrl($attachmentId, self::SIZE_NAME);
        }

        $fallback = $this->smallestCovering($attachmentId);

        return $fallback === null ? '' : $this->sizeUrl($attachmentId, $fallback);
    }

    public function ensure(int $attachmentId): bool
    {
        if ($attachmentId <= 0) {
            return false;
        }

        $meta = $this->metadata($attachmentId);
        if ($meta === null) {
            return false;
        }

        $sizes = self::sizes($meta);
        if (isset($sizes[self::SIZE_NAME])) {
            return true;
        }

        $file = get_attached_file($attachmentId);
        if (!is_string($file) || $file === '') {
            return false;
        }

        $made = image_make_intermediate_size($file, self::SIZE, self::SIZE, true);
        if (!is_array($made)) {
            return false;
        }

        $sizes[self::SIZE_NAME] = $made;
        $meta['sizes'] = $sizes;
        wp_update_attachment_metadata($attachmentId, $meta);

        return true;
    }

    private function smallestCovering(int $attachmentId): ?string
    {
        $meta = $this->metadata($attachmentId);
        if ($meta === null) {
            return null;
        }

        $best = null;
        $bestArea = PHP_INT_MAX;

        foreach (self::sizes($meta) as $name => $size) {
            $width = is_array($size) && is_numeric($size['width'] ?? null) ? (int) $size['width'] : 0;
            $height = is_array($size) && is_numeric($size['height'] ?? null) ? (int) $size['height'] : 0;

            if ($width >= self::SIZE && $height >= self::SIZE && $width * $height < $bestArea) {
                $best = (string) $name;
                $bestArea = $width * $height;
            }
        }

        return $best;
    }

    private function sizeUrl(int $attachmentId, string $size): string
    {
        $url = wp_get_attachment_image_url($attachmentId, $size);

        return is_string($url) ? $url : '';
    }

    private function metadata(int $attachmentId): ?array
    {
        $meta = wp_get_attachment_metadata($attachmentId);

        return is_array($meta) ? $meta : null;
    }

    private static function sizes(array $meta): array
    {
        return is_array($meta['sizes'] ?? null) ? $meta['sizes'] : [];
    }
}
