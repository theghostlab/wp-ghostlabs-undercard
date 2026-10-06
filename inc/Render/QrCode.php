<?php

namespace GHOSTLABS\UNDERCARD\Render;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Exception\ExceptionInterface;

final class QrCode
{
    
    private const QUIET_ZONE = 4;

    private const ERROR_CORRECTION = 'M';

    public function svg(string $url, string $class = ''): string
    {
        $matrix = $this->matrix($url);

        if ($matrix === null) {
            return '';
        }

        $side = count($matrix) + self::QUIET_ZONE * 2;
        $path = $this->path($matrix);

        $attributes = sprintf(
            'viewBox="0 0 %1$d %1$d" width="%1$d" height="%1$d"'
            . ' xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges"'
            . ' role="img" aria-hidden="true" focusable="false"',
            $side,
        );

        if ($class !== '') {
            $attributes = 'class="' . esc_attr($class) . '" ' . $attributes;
        }

        return '<svg ' . $attributes . '>'
            . '<rect width="100%" height="100%" fill="var(--ghostlabs-u-qr-quiet, #fff)"/>'
            . '<path d="' . $path . '" fill="var(--ghostlabs-u-qr-ink, #000)"/>'
            . '</svg>';
    }

    private function matrix(string $url): ?array
    {
        if (trim($url) === '') {
            return null;
        }

        try {
            $matrix = Encoder::encode(
                $url,
                ErrorCorrectionLevel::valueOf(self::ERROR_CORRECTION),
            )->getMatrix();
        } catch (ExceptionInterface) {

            return null;
        }

        $rows = [];

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            $row = [];

            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                $row[] = $matrix->get($x, $y) === 1;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function path(array $matrix): string
    {
        $parts = [];

        foreach ($matrix as $y => $row) {
            $runStart = null;

            foreach ($row as $x => $dark) {
                if ($dark && $runStart === null) {
                    $runStart = $x;
                }

                if (! $dark && $runStart !== null) {
                    $parts[] = $this->rect($runStart, $y, $x - $runStart);
                    $runStart = null;
                }
            }

            if ($runStart !== null) {
                $parts[] = $this->rect($runStart, $y, count($row) - $runStart);
            }
        }

        return implode('', $parts);
    }

    private function rect(int $x, int $y, int $width): string
    {
        return sprintf(
            'M%d %dh%dv1h-%dz',
            $x + self::QUIET_ZONE,
            $y + self::QUIET_ZONE,
            $width,
            $width,
        );
    }
}
