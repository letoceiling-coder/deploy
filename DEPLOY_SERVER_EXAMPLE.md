# Пример серверного endpoint для деплоя

Этот документ описывает, как должен быть реализован серверный endpoint для приема запросов на деплой от пакета `laravel-deploy`.

## 📍 Endpoint

**URL:** `{DEPLOY_SERVER_URL}` (из .env)  
**Method:** `POST`  
**Content-Type:** `application/json`

## 🔐 Авторизация

Запрос должен содержать заголовок:

```
Authorization: Bearer {DEPLOY_TOKEN}
```

## 📥 Тело запроса

```json
{
  "branch": "main",
  "version": "abc1234",
  "with_seed": false
}
```

### Параметры

| Параметр | Тип | Описание |
|----------|-----|----------|
| `branch` | string | Ветка Git для деплоя |
| `version` | string | Версия/тег/commit hash |
| `with_seed` | boolean | Выполнить `php artisan db:seed` |

## 🔄 Логика сервера

Сервер должен выполнить следующие шаги:

### 1. Проверка авторизации

```php
$token = $request->bearerToken();
if ($token !== env('DEPLOY_TOKEN')) {
    return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
}
```

### 2. Git Pull

```bash
cd /path/to/project
git fetch origin
git checkout {branch}
git pull origin {branch}
```

### 3. Composer Install

```bash
composer install --no-dev --optimize-autoloader
```

**Важно:** Используйте правильный путь к PHP 8.2 на Beget:
```bash
/usr/local/bin/php8.2 ~/bin/composer.phar install --no-dev
```

### 4. NPM Install & Build

```bash
npm install
npm run build
```

**Важно:** На Beget используйте nvm:
```bash
source ~/.bashrc
npm install
npm run build
```

### 5. Миграции

```bash
php artisan migrate --force
```

### 6. Seeders (если `with_seed: true`)

```bash
php artisan db:seed --force
```

### 7. Очистка кэша

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📤 Ответ сервера

### Успешный деплой

```json
{
  "success": true,
  "message": "Deployment completed successfully",
  "version": "abc1234",
  "branch": "main",
  "steps": {
    "git_pull": {
      "status": "success",
      "output": "Already up to date."
    },
    "composer_install": {
      "status": "success",
      "output": "Package operations: 0 installs, 0 updates, 0 removals"
    },
    "npm_install": {
      "status": "success",
      "output": "added 133 packages"
    },
    "npm_build": {
      "status": "success",
      "output": "Build completed"
    },
    "migrate": {
      "status": "success",
      "output": "Nothing to migrate."
    },
    "optimize": {
      "status": "success"
    }
  },
  "timestamp": "2024-12-24 12:30:45"
}
```

### Ошибка деплоя

```json
{
  "success": false,
  "message": "Deployment failed",
  "error": "Migration failed: SQLSTATE[42S22]: Column not found",
  "step": "migrate",
  "steps": {
    "git_pull": {
      "status": "success"
    },
    "composer_install": {
      "status": "success"
    },
    "migrate": {
      "status": "error",
      "error": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'deleted_at' in 'where clause'",
      "output": "..."
    }
  },
  "timestamp": "2024-12-24 12:30:45"
}
```

## 💻 Пример реализации (Laravel Controller)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    public function deploy(Request $request)
    {
        // 1. Проверка токена
        $token = $request->bearerToken();
        if ($token !== env('DEPLOY_TOKEN')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $branch = $request->input('branch', 'main');
        $version = $request->input('version');
        $withSeed = $request->input('with_seed', false);

        $steps = [];
        $projectPath = base_path();

        try {
            // 2. Git Pull
            $steps['git_pull'] = $this->gitPull($projectPath, $branch);

            // 3. Composer Install
            $steps['composer_install'] = $this->composerInstall($projectPath);

            // 4. NPM Install & Build
            $steps['npm_install'] = $this->npmInstall($projectPath);
            $steps['npm_build'] = $this->npmBuild($projectPath);

            // 5. Миграции
            $steps['migrate'] = $this->runMigrations();

            // 6. Seeders (если нужно)
            if ($withSeed) {
                $steps['seed'] = $this->runSeeders();
            }

            // 7. Очистка кэша
            $steps['optimize'] = $this->optimize();

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
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Deployment failed',
                'error' => $e->getMessage(),
                'steps' => $steps,
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    private function gitPull(string $path, string $branch): array
    {
        $result = Process::path($path)
            ->run("git fetch origin && git checkout {$branch} && git pull origin {$branch}");

        return [
            'status' => $result->successful() ? 'success' : 'error',
            'output' => $result->output(),
            'error' => $result->successful() ? null : $result->errorOutput()
        ];
    }

    private function composerInstall(string $path): array
    {
        // На Beget используйте полный путь к composer
        $composerPath = env('COMPOSER_PATH', 'composer');
        
        $result = Process::path($path)
            ->timeout(300)
            ->run("{$composerPath} install --no-dev --optimize-autoloader");

        return [
            'status' => $result->successful() ? 'success' : 'error',
            'output' => $result->output(),
            'error' => $result->successful() ? null : $result->errorOutput()
        ];
    }

    private function npmInstall(string $path): array
    {
        $result = Process::path($path)
            ->timeout(300)
            ->run('npm install');

        return [
            'status' => $result->successful() ? 'success' : 'error',
            'output' => $result->output(),
            'error' => $result->successful() ? null : $result->errorOutput()
        ];
    }

    private function npmBuild(string $path): array
    {
        $result = Process::path($path)
            ->timeout(600)
            ->run('npm run build');

        return [
            'status' => $result->successful() ? 'success' : 'error',
            'output' => $result->output(),
            'error' => $result->successful() ? null : $result->errorOutput()
        ];
    }

    private function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'status' => 'success',
                'output' => $output
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function runSeeders(): array
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();

            return [
                'status' => 'success',
                'output' => $output
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function optimize(): array
    {
        try {
            Artisan::call('optimize:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return [
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
}
```

## 🛣️ Route

Добавьте в `routes/api.php`:

```php
Route::post('/deploy', [DeployController::class, 'deploy']);
```

## 🔒 Безопасность

1. **Токен:** Храните `DEPLOY_TOKEN` в `.env`, никогда не коммитьте в Git
2. **HTTPS:** Используйте HTTPS для endpoint
3. **IP Whitelist:** (Опционально) Ограничьте доступ по IP
4. **Rate Limiting:** Добавьте rate limiting для защиты от DDoS

## 📝 Логирование на сервере

Рекомендуется логировать все шаги деплоя:

```php
Log::info('Deployment started', [
    'branch' => $branch,
    'version' => $version,
    'ip' => $request->ip()
]);

// После каждого шага
Log::info('Git pull completed', ['output' => $output]);
Log::info('Composer install completed', ['output' => $output]);
// и т.д.
```

## ⚙️ Настройка для Beget

Учитывая особенности Beget (см. `SERVER_SETUP.md`):

### .env на сервере

```env
COMPOSER_PATH=/home/l/letocewh/bin/composer
PHP_PATH=/usr/local/bin/php8.2
NVM_PATH=/home/l/letocewh/.nvm
```

### Использование в коде

```php
// Composer
$composer = env('COMPOSER_PATH', 'composer');
Process::run("{$composer} install --no-dev");

// PHP
$php = env('PHP_PATH', 'php');
Artisan::call('migrate', ['--force' => true]); // Laravel использует правильный PHP автоматически

// NPM (уже в PATH через nvm)
Process::run('npm install');
```

## ✅ Чеклист

- [ ] Endpoint принимает POST запросы
- [ ] Проверка токена авторизации
- [ ] Git pull выполняется корректно
- [ ] Composer install использует правильный путь
- [ ] NPM команды работают (nvm загружен)
- [ ] Миграции выполняются с `--force`
- [ ] Seeders выполняются только при флаге
- [ ] Кэш очищается и пересоздается
- [ ] Все ошибки логируются
- [ ] JSON ответ содержит статусы всех шагов

