<?php

declare(strict_types=1);

namespace GHOSTLABS\UNDERCARD\Service\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The unit-test seam around core's `Plugin_Upgrader` machinery.
 *
 * `UpgradeOrchestrator` talks to this so its ordering rules can be asserted
 * against a fake; the real `PackageInstaller` is core behaving as core behaves
 * and is proven end to end instead.
 */
interface PackageInstallerInterface
{
    /**
     * Environment checks that must pass before anything is attempted.
     *
     * @return StepResult|null a failure, or null when clear to proceed.
     */
    public function preflight(): ?StepResult;

    /**
     * Install the package at `$zipUrl`, or recognize one already on disk.
     * On success the step's value is the installed plugin's basename.
     */
    public function install(string $zipUrl): StepResult;
}
