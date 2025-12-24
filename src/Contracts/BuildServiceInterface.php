<?php

namespace LetoceilingCoder\Deploy\Contracts;

interface BuildServiceInterface
{
    /**
     * Check if npm is available
     */
    public function isNpmAvailable(): bool;

    /**
     * Check if package.json exists
     */
    public function hasPackageJson(): bool;

    /**
     * Run npm install
     */
    public function install(bool $dryRun = false): bool;

    /**
     * Run npm build
     */
    public function build(bool $dryRun = false): bool;
}

