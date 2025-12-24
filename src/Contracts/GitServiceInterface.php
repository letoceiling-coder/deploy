<?php

namespace LetoceilingCoder\Deploy\Contracts;

use LetoceilingCoder\Deploy\DTO\GitCommitDTO;

interface GitServiceInterface
{
    /**
     * Check if git is available
     */
    public function isAvailable(): bool;

    /**
     * Check if repository is initialized
     */
    public function isRepositoryInitialized(): bool;

    /**
     * Get current branch
     */
    public function getCurrentBranch(): string;

    /**
     * Get current commit hash
     */
    public function getCurrentCommitHash(): string;

    /**
     * Stage all changes
     */
    public function stageAll(): bool;

    /**
     * Commit changes
     */
    public function commit(GitCommitDTO $dto): bool;

    /**
     * Push to remote
     */
    public function push(string $remote, string $branch, bool $dryRun = false): bool;

    /**
     * Get repository URL
     */
    public function getRepositoryUrl(): ?string;

    /**
     * Check if there are uncommitted changes
     */
    public function hasUncommittedChanges(): bool;
}

