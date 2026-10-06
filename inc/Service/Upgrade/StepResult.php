<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What one step of the upgrade came to: a value on success, a notice code and
 * a sentence on failure.
 *
 * Each step has exactly one thing to hand on — the claim exchange a key, the
 * locator a package URL, the installer a plugin basename, the verifier nothing
 * at all — so one shape serves all four and the orchestrator reads them alike.
 * Failures are values here for the same reason they are in `UpgradeResult`: the
 * page renders per-code guidance, and a failure that arrived as an exception
 * would reach it as silence.
 *
 * A `final class` with promoted readonly properties rather than a `readonly
 * class`, which is PHP 8.2 syntax; the plugin's floor is 8.1.
 */
final class StepResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly ?string $value,
        public readonly string $noticeCode,
        public readonly string $message,
    ) {
    }

    public static function success(?string $value = null): self
    {
        return new self(true, $value, '', '');
    }

    public static function failure(string $noticeCode, string $message): self
    {
        return new self(false, null, $noticeCode, $message);
    }
}
