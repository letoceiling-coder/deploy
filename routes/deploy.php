<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Deploy Endpoint
|--------------------------------------------------------------------------
|
| Этот endpoint принимает запросы от пакета letoceiling-coder/deploy
| и выполняет деплой на сервере.
|
| Автоматически подключается при установке пакета.
|
*/

Route::post('/deploy', function (Request $request) {
    // 1. Проверка токена
    $token = $request->bearerToken();
    $expectedToken = env('DEPLOY_TOKEN');
    
    if (empty($token) || $token !== $expectedToken) {
        Log::warning('Deploy request rejected: Invalid token', [
            'ip' => $request->ip(),
            'provided_token' => substr($token ?? '', 0, 4) . '...'
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    $branch = $request->input('branch', 'main');
    $version = $request->input('version', 'unknown');
    $withSeed = $request->input('with_seed', false);

    Log::info('Deploy request received', [
        'branch' => $branch,
        'version' => $version,
        'with_seed' => $withSeed,
        'ip' => $request->ip()
    ]);

    $steps = [];
    $projectPath = base_path();

    try {
        // 2. Git Pull
        Log::info('Starting git pull');
        $gitPull = Process::path($projectPath)
            ->timeout(300)
            ->run("git fetch origin && git checkout {$branch} && git pull origin {$branch}");
        
        $steps['git_pull'] = [
            'status' => $gitPull->successful() ? 'success' : 'error',
            'output' => $gitPull->output(),
            'error' => $gitPull->successful() ? null : $gitPull->errorOutput()
        ];

        if (!$gitPull->successful()) {
            throw new \Exception('Git pull failed: ' . $gitPull->errorOutput());
        }

        // 3. Composer Install
        Log::info('Starting composer install');
        $composerPath = env('COMPOSER_PATH', 'composer');
        $composerInstall = Process::path($projectPath)
            ->timeout(300)
            ->run("{$composerPath} install --no-dev --optimize-autoloader");
        
        $steps['composer_install'] = [
            'status' => $composerInstall->successful() ? 'success' : 'error',
            'output' => $composerInstall->output(),
            'error' => $composerInstall->successful() ? null : $composerInstall->errorOutput()
        ];

        if (!$composerInstall->successful()) {
            throw new \Exception('Composer install failed: ' . $composerInstall->errorOutput());
        }

        // 4. NPM Install & Build
        Log::info('Starting npm install');
        $npmInstall = Process::path($projectPath)
            ->timeout(300)
            ->run('npm install');
        
        $steps['npm_install'] = [
            'status' => $npmInstall->successful() ? 'success' : 'error',
            'output' => $npmInstall->output(),
            'error' => $npmInstall->successful() ? null : $npmInstall->errorOutput()
        ];

        if (!$npmInstall->successful()) {
            throw new \Exception('npm install failed: ' . $npmInstall->errorOutput());
        }

        Log::info('Starting npm build');
        $npmBuild = Process::path($projectPath)
            ->timeout(600)
            ->run('npm run build');
        
        $steps['npm_build'] = [
            'status' => $npmBuild->successful() ? 'success' : 'error',
            'output' => $npmBuild->output(),
            'error' => $npmBuild->successful() ? null : $npmBuild->errorOutput()
        ];

        if (!$npmBuild->successful()) {
            throw new \Exception('npm build failed: ' . $npmBuild->errorOutput());
        }

        // 5. Миграции
        Log::info('Running migrations');
        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = Artisan::output();
            $steps['migrate'] = [
                'status' => 'success',
                'output' => $migrateOutput
            ];
        } catch (\Exception $e) {
            $steps['migrate'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            throw $e;
        }

        // 6. Seeders (если нужно)
        if ($withSeed) {
            Log::info('Running seeders');
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $seedOutput = Artisan::output();
                $steps['seed'] = [
                    'status' => 'success',
                    'output' => $seedOutput
                ];
            } catch (\Exception $e) {
                $steps['seed'] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                throw $e;
            }
        }

        // 7. Очистка кэша
        Log::info('Optimizing application');
        try {
            Artisan::call('optimize:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $steps['optimize'] = [
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            $steps['optimize'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            // Не бросаем исключение, т.к. это не критично
        }

        Log::info('Deployment completed successfully', [
            'branch' => $branch,
            'version' => $version
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deployment completed successfully',
            'version' => $version,
            'branch' => $branch,
            'steps' => $steps,
            'timestamp' => now()->toDateTimeString()
        ]);

    } catch (\Exception $e) {
        Log::error('Deployment failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'branch' => $branch,
            'version' => $version
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Deployment failed',
            'error' => $e->getMessage(),
            'steps' => $steps,
            'timestamp' => now()->toDateTimeString()
        ], 500);
    }
})->middleware('api');

