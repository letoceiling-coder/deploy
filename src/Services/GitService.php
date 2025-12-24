<?php

namespace LetoceilingCoder\Deploy\Services;

use Illuminate\Support\Facades\Process;
use LetoceilingCoder\Deploy\Contracts\GitServiceInterface;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;
use LetoceilingCoder\Deploy\DTO\GitCommitDTO;
use LetoceilingCoder\Deploy\Exceptions\GitException;

class GitService implements GitServiceInterface
{
    public function __construct(
        private LoggerServiceInterface $logger
    ) {
    }

    public function isAvailable(): bool
    {
        $result = Process::run('git --version');
        return $result->successful();
    }

    public function isRepositoryInitialized(): bool
    {
        return is_dir(base_path('.git'));
    }

    public function getCurrentBranch(): string
    {
        $result = Process::run('git rev-parse --abbrev-ref HEAD');
        
        if (!$result->successful()) {
            // Если нет коммитов, попробуем получить ветку из конфига
            $configResult = Process::run('git config --get init.defaultBranch');
            if ($configResult->successful() && !empty(trim($configResult->output()))) {
                return trim($configResult->output());
            }
            // По умолчанию используем main
            return 'main';
        }

        return trim($result->output());
    }

    public function getCurrentCommitHash(): string
    {
        $result = Process::run('git rev-parse HEAD');
        
        if (!$result->successful()) {
            // Если нет коммитов, возвращаем пустую строку
            // Это нормально для нового репозитория
            return '';
        }

        return trim($result->output());
    }

    public function stageAll(): bool
    {
        $this->logger->step('GIT', 'Staging all changes');
        
        $result = Process::run('git add .');
        
        if (!$result->successful()) {
            $this->logger->error('Failed to stage changes', [
                'error' => $result->errorOutput()
            ]);
            throw new GitException('Failed to stage changes: ' . $result->errorOutput());
        }

        $this->logger->info('All changes staged successfully');
        return true;
    }

    public function commit(GitCommitDTO $dto): bool
    {
        $this->logger->step('GIT', "Committing changes: {$dto->message}");
        
        $command = 'git commit';
        
        if ($dto->allowEmpty) {
            $command .= ' --allow-empty';
        }
        
        $command .= ' -m ' . escapeshellarg($dto->message);
        
        $result = Process::run($command);
        
        if (!$result->successful()) {
            // Check if there's nothing to commit
            if (str_contains($result->errorOutput(), 'nothing to commit')) {
                $this->logger->warning('Nothing to commit');
                return false;
            }
            
            $this->logger->error('Failed to commit', [
                'error' => $result->errorOutput()
            ]);
            throw new GitException('Failed to commit: ' . $result->errorOutput());
        }

        $this->logger->info('Changes committed successfully');
        return true;
    }

    public function push(string $remote, string $branch, bool $dryRun = false): bool
    {
        if ($dryRun) {
            $this->logger->step('GIT', "[DRY-RUN] Would push to {$remote}/{$branch}");
            return true;
        }

        $this->logger->step('GIT', "Pushing to {$remote}/{$branch}");
        
        // Ensure remote is set
        $repositoryUrl = env('GIT_REPOSITORY_URL');
        if ($repositoryUrl) {
            // Check if remote exists, if not add it
            $checkRemote = Process::run("git remote get-url {$remote}");
            if (!$checkRemote->successful()) {
                Process::run("git remote add {$remote} {$repositoryUrl}");
            } else {
                // Update remote URL if it changed
                Process::run("git remote set-url {$remote} {$repositoryUrl}");
            }
        }
        
        $result = Process::run("git push {$remote} {$branch}");
        
        if (!$result->successful()) {
            $this->logger->error('Failed to push', [
                'remote' => $remote,
                'branch' => $branch,
                'error' => $result->errorOutput()
            ]);
            throw new GitException('Failed to push: ' . $result->errorOutput());
        }

        $this->logger->info('Successfully pushed to remote');
        return true;
    }

    public function getRepositoryUrl(): ?string
    {
        $result = Process::run('git config --get remote.origin.url');
        
        if (!$result->successful()) {
            return null;
        }

        return trim($result->output());
    }

    public function hasUncommittedChanges(): bool
    {
        $result = Process::run('git status --porcelain');
        return !empty(trim($result->output()));
    }
}

