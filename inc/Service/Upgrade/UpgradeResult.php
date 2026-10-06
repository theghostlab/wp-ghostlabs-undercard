<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What one upgrade attempt came to, as data — and the exact shape both REST
 * routes answer:
 *
 *     { ok: bool, stage: string, noticeCode: string|null, message: string, activationUrl: string|null }
 *
 * Every failure is a value, never an exception or an HTTP error status: the
 * settings page renders `noticeCode`-specific guidance in its status region,
 * and a failure that arrived as anything but data is one the page would show
 * as silence. A success carries no notice code and the activation URL the
 * browser follows next.
 *
 * `stage` is where the run stopped: `preflight`, `claim`, `verify`, `download`,
 * `install`, or `complete`.
 *
 * A `final class` with promoted readonly properties rather than a `readonly
 * class`, which is PHP 8.2 syntax; the plugin's floor is 8.1.
 */
final class UpgradeResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly string $stage,
        public readonly ?string $noticeCode,
        public readonly string $message,
        public readonly ?string $activationUrl,
    ) {
    }

    public static function complete(string $activationUrl): self
    {
        return new self(true, 'complete', null, '', $activationUrl);
    }

    public static function fail(string $stage, string $noticeCode, string $message): self
    {
        return new self(false, $stage, $noticeCode, $message, null);
    }

    /**
     * A failed step, carried into the stage it failed at.
     */
    public static function failedAt(string $stage, StepResult $step): self
    {
        return self::fail($stage, $step->noticeCode, $step->message);
    }

    /** @return array{ok: bool, stage: string, noticeCode: string|null, message: string, activationUrl: string|null} */
    public function toArray(): array
    {
        return [
            'ok'            => $this->ok,
            'stage'         => $this->stage,
            'noticeCode'    => $this->noticeCode,
            'message'       => $this->message,
            'activationUrl' => $this->activationUrl,
        ];
    }
}
