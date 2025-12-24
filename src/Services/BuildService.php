<?php

namespace LetoceilingCoder\Deploy\Services;

use Illuminate\Support\Facades\Process;
use LetoceilingCoder\Deploy\Contracts\BuildServiceInterface;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;
use LetoceilingCoder\Deploy\Exceptions\BuildException;

class BuildService implements BuildServiceInterface
{
    public function __construct(
        private LoggerServiceInterface $logger
    ) {
    }

    public function isNpmAvailable(): bool
    {
        $result = Process::run('npm --version');
        return $result->successful();
    }

    public function hasPackageJson(): bool
    {
        return file_exists(base_path('package.json'));
    }

    public function install(bool $dryRun = false): bool
    {
        if ($dryRun) {
            $this->logger->step('BUILD', '[DRY-RUN] Would run: npm install');
            return true;
        }

        $this->logger->step('BUILD', 'Running npm install');
        
        $result = Process::timeout(300)->run('npm install');
        
        if (!$result->successful()) {
            $this->logger->error('npm install failed', [
                'error' => $result->errorOutput()
            ]);
            throw new BuildException('npm install failed: ' . $result->errorOutput());
        }

        $this->logger->info('npm install completed successfully');
        return true;
    }

    public function build(bool $dryRun = false): bool
    {
        if ($dryRun) {
            $this->logger->step('BUILD', '[DRY-RUN] Would run: npm run build');
            return true;
        }

        $this->logger->step('BUILD', 'Running npm run build');
        
        $result = Process::timeout(600)->run('npm run build');
        
        if (!$result->successful()) {
            $this->logger->error('npm run build failed', [
                'error' => $result->errorOutput(),
                'output' => $result->output()
            ]);
            throw new BuildException('npm run build failed: ' . $result->errorOutput());
        }

        $this->logger->info('npm run build completed successfully');
        return true;
    }
}

