<?php

namespace LetoceilingCoder\Deploy\Commands;

use Illuminate\Console\Command;
use LetoceilingCoder\Deploy\Contracts\BuildServiceInterface;
use LetoceilingCoder\Deploy\Contracts\GitServiceInterface;
use LetoceilingCoder\Deploy\Contracts\HttpDeployServiceInterface;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;
use LetoceilingCoder\Deploy\DTO\DeployOptionsDTO;
use LetoceilingCoder\Deploy\DTO\DeployRequestDTO;
use LetoceilingCoder\Deploy\DTO\GitCommitDTO;
use LetoceilingCoder\Deploy\Exceptions\BuildException;
use LetoceilingCoder\Deploy\Exceptions\ConfigurationException;
use LetoceilingCoder\Deploy\Exceptions\GitException;
use LetoceilingCoder\Deploy\Exceptions\HttpDeployException;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deploy
                            {--message= : Custom commit message}
                            {--skip-build : Skip npm run build}
                            {--dry-run : Show all steps without execution}
                            {--insecure : Disable SSL verification}
                            {--with-seed : Execute seeders on server}
                            {--branch= : Branch to deploy}
                            {--version= : Specific version/tag to deploy}';

    /**
     * The console command description.
     */
    protected $description = 'Deploy Laravel project to server';

    public function __construct(
        private GitServiceInterface $gitService,
        private BuildServiceInterface $buildService,
        private HttpDeployServiceInterface $httpDeployService,
        private LoggerServiceInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting deployment process...');
        $this->logger->step('DEPLOY', 'Deployment started');

        try {
            // Parse options
            $options = $this->parseOptions();

            if ($options->dryRun) {
                $this->warn('🔍 DRY-RUN mode: No changes will be made');
                $this->logger->step('DEPLOY', 'DRY-RUN mode enabled');
            }

            // Step 1: Validate environment
            $this->validateEnvironment($options);

            // Step 2: Validate configuration
            $this->validateConfiguration();

            // Step 3: Git operations
            $branch = $this->handleGitOperations($options);

            // Step 4: Build (if not skipped)
            if (!$options->skipBuild) {
                $this->handleBuild($options);
            } else {
                $this->info('⏭️  Skipping build (--skip-build flag)');
                $this->logger->step('BUILD', 'Build skipped by flag');
            }

            // Step 5: Send deploy request
            $this->handleDeployRequest($options, $branch);

            $this->info('✅ Deployment completed successfully!');
            $this->logger->step('DEPLOY', 'Deployment completed successfully');

            return Command::SUCCESS;

        } catch (ConfigurationException $e) {
            $this->error('❌ Configuration error: ' . $e->getMessage());
            $this->logger->error('Configuration error', ['message' => $e->getMessage()]);
            return Command::FAILURE;

        } catch (GitException $e) {
            $this->error('❌ Git error: ' . $e->getMessage());
            $this->logger->error('Git error', ['message' => $e->getMessage()]);
            return Command::FAILURE;

        } catch (BuildException $e) {
            $this->error('❌ Build error: ' . $e->getMessage());
            $this->logger->error('Build error', ['message' => $e->getMessage()]);
            return Command::FAILURE;

        } catch (HttpDeployException $e) {
            $this->error('❌ Deploy request error: ' . $e->getMessage());
            $this->logger->error('Deploy request error', ['message' => $e->getMessage()]);
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('❌ Unexpected error: ' . $e->getMessage());
            $this->logger->error('Unexpected error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Parse command options into DTO
     */
    private function parseOptions(): DeployOptionsDTO
    {
        return new DeployOptionsDTO(
            message: $this->option('message'),
            skipBuild: $this->option('skip-build'),
            dryRun: $this->option('dry-run'),
            insecure: $this->option('insecure'),
            withSeed: $this->option('with-seed'),
            branch: $this->option('branch'),
            version: $this->option('version')
        );
    }

    /**
     * Validate environment (git, composer, npm)
     */
    private function validateEnvironment(DeployOptionsDTO $options): void
    {
        $this->info('🔍 Validating environment...');
        $this->logger->step('VALIDATE', 'Validating environment');

        // Check git
        if (!$this->gitService->isAvailable()) {
            throw new ConfigurationException('Git is not available. Please install git.');
        }
        $this->info('  ✓ Git is available');

        if (!$this->gitService->isRepositoryInitialized()) {
            throw new ConfigurationException('Git repository is not initialized. Run: git init');
        }
        $this->info('  ✓ Git repository initialized');

        // Check npm (only if build is not skipped)
        if (!$options->skipBuild) {
            if (!$this->buildService->isNpmAvailable()) {
                throw new ConfigurationException('npm is not available. Please install Node.js and npm.');
            }
            $this->info('  ✓ npm is available');

            if (!$this->buildService->hasPackageJson()) {
                $this->warn('  ⚠ package.json not found. Build will be skipped.');
            } else {
                $this->info('  ✓ package.json found');
            }
        }

        // Check .env file
        if (!file_exists(base_path('.env'))) {
            throw new ConfigurationException('.env file not found');
        }
        $this->info('  ✓ .env file found');

        $this->logger->step('VALIDATE', 'Environment validation passed');
    }

    /**
     * Validate configuration (.env variables)
     */
    private function validateConfiguration(): void
    {
        $this->info('🔍 Validating configuration...');
        $this->logger->step('VALIDATE', 'Validating configuration');

        $required = [
            'GIT_REPOSITORY_URL' => env('GIT_REPOSITORY_URL'),
            'DEPLOY_SERVER_URL' => env('DEPLOY_SERVER_URL'),
            'DEPLOY_TOKEN' => env('DEPLOY_TOKEN'),
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new ConfigurationException(
                'Missing required environment variables: ' . implode(', ', $missing)
            );
        }

        // Validate HTTP service configuration
        $this->httpDeployService->validateConfiguration();

        $this->info('  ✓ All required configuration present');
        $this->logger->step('VALIDATE', 'Configuration validation passed');
    }

    /**
     * Handle git operations (stage, commit, push)
     */
    private function handleGitOperations(DeployOptionsDTO $options): string
    {
        $this->info('📦 Processing Git operations...');
        $this->logger->step('GIT', 'Starting git operations');

        // Get current branch or use specified branch
        $branch = $options->branch ?? $this->gitService->getCurrentBranch();
        $this->info("  Branch: {$branch}");

        // Check for uncommitted changes
        if (!$options->dryRun && $this->gitService->hasUncommittedChanges()) {
            $this->info('  Staging changes...');
            $this->gitService->stageAll();

            // Commit message
            $commitMessage = $options->message ?? $this->generateDefaultCommitMessage();
            $this->info("  Committing: {$commitMessage}");

            $commitDTO = new GitCommitDTO(
                message: $commitMessage,
                allowEmpty: false
            );

            $committed = $this->gitService->commit($commitDTO);
            
            if (!$committed) {
                $this->warn('  No changes to commit');
            }
        } else {
            if ($options->dryRun) {
                $this->info('  [DRY-RUN] Would stage and commit changes');
            } else {
                $this->info('  No uncommitted changes');
            }
        }

        // Get commit hash for version
        $commitHash = $this->gitService->getCurrentCommitHash();
        $version = $options->version ?? substr($commitHash, 0, 7);
        $this->info("  Version: {$version}");

        // Push to remote
        $remoteUrl = env('GIT_REPOSITORY_URL');
        $this->info("  Pushing to: {$remoteUrl}");

        // Extract remote name (usually 'origin')
        $remoteName = 'origin';
        $this->gitService->push($remoteName, $branch, $options->dryRun);

        if (!$options->dryRun) {
            $this->info('  ✓ Successfully pushed to remote');
        }

        $this->logger->step('GIT', 'Git operations completed', [
            'branch' => $branch,
            'version' => $version,
            'commit' => $commitHash
        ]);

        return $branch;
    }

    /**
     * Handle build process
     */
    private function handleBuild(DeployOptionsDTO $options): void
    {
        $this->info('🔨 Building assets...');
        $this->logger->step('BUILD', 'Starting build process');

        if (!$this->buildService->hasPackageJson()) {
            $this->warn('  ⚠ package.json not found, skipping build');
            return;
        }

        // npm install (optional, but recommended)
        $this->info('  Running npm install...');
        $this->buildService->install($options->dryRun);

        // npm run build
        $this->info('  Running npm run build...');
        $this->buildService->build($options->dryRun);

        if (!$options->dryRun) {
            $this->info('  ✓ Build completed successfully');
        }
    }

    /**
     * Handle deploy HTTP request
     */
    private function handleDeployRequest(DeployOptionsDTO $options, string $branch): void
    {
        $this->info('🌐 Sending deploy request to server...');
        $this->logger->step('HTTP', 'Sending deploy request');

        $serverUrl = env('DEPLOY_SERVER_URL');
        $token = env('DEPLOY_TOKEN');
        $version = $options->version ?? substr($this->gitService->getCurrentCommitHash(), 0, 7);

        if ($options->dryRun) {
            $this->info("  [DRY-RUN] Would send POST request to: {$serverUrl}");
            $this->info("  [DRY-RUN] Headers: Authorization: Bearer ***");
            $this->info("  [DRY-RUN] Body: branch={$branch}, version={$version}, with_seed=" . ($options->withSeed ? 'true' : 'false'));
            return;
        }

        $dto = new DeployRequestDTO(
            serverUrl: $serverUrl,
            token: $token,
            branch: $branch,
            version: $version,
            withSeed: $options->withSeed,
            insecure: $options->insecure,
            timeout: 300
        );

        $response = $this->httpDeployService->deploy($dto);

        if ($response['success']) {
            $this->info('  ✓ Deploy request sent successfully');
            $this->info('  Server response: ' . json_encode($response['data'], JSON_PRETTY_PRINT));
        } else {
            throw new HttpDeployException('Deploy request failed');
        }
    }

    /**
     * Generate default commit message
     */
    private function generateDefaultCommitMessage(): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        return "Deploy: {$timestamp}";
    }
}

