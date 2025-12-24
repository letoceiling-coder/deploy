<?php

namespace LetoceilingCoder\Deploy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LetoceilingCoder\Deploy\Commands\DeployCommand;
use LetoceilingCoder\Deploy\Contracts\BuildServiceInterface;
use LetoceilingCoder\Deploy\Contracts\GitServiceInterface;
use LetoceilingCoder\Deploy\Contracts\HttpDeployServiceInterface;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;
use LetoceilingCoder\Deploy\Services\BuildService;
use LetoceilingCoder\Deploy\Services\GitService;
use LetoceilingCoder\Deploy\Services\HttpDeployService;
use LetoceilingCoder\Deploy\Services\LoggerService;

class DeployServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind contracts to implementations
        $this->app->singleton(LoggerServiceInterface::class, LoggerService::class);
        $this->app->singleton(GitServiceInterface::class, GitService::class);
        $this->app->singleton(BuildServiceInterface::class, BuildService::class);
        $this->app->singleton(HttpDeployServiceInterface::class, HttpDeployService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Merge config file
        $this->mergeConfigFrom(
            __DIR__ . '/../config/deploy.php',
            'deploy'
        );

        // Load routes automatically with /api prefix
        Route::prefix('api')->middleware('api')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/deploy.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployCommand::class,
            ]);

            // Publish config file (optional)
            $this->publishes([
                __DIR__ . '/../config/deploy.php' => config_path('deploy.php'),
            ], 'deploy-config');

            // Publish routes file (optional, for customization)
            $this->publishes([
                __DIR__ . '/../routes/deploy.php' => base_path('routes/deploy.php'),
            ], 'deploy-routes');
        }
    }
}

