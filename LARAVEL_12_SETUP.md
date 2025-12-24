# 🔧 Настройка для Laravel 12

В Laravel 12 структура изменилась. Нужно явно указать загрузку API роутов в `bootstrap/app.php`.

## Шаг 1: Откройте `bootstrap/app.php`

```bash
nano bootstrap/app.php
```

## Шаг 2: Добавьте загрузку API роутов

Найдите строку с `->withRouting(` и добавьте `api`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // ← Добавьте эту строку
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
```

## Шаг 3: Исправьте `routes/api.php`

Убедитесь, что файл содержит только:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LetoceilingCoder\Deploy\DeployController;

Route::post('/deploy', [DeployController::class, 'handle']);
```

## Шаг 4: Очистите кэш

```bash
php artisan route:clear
php artisan config:clear
```

## Шаг 5: Проверка

```bash
# Проверить роуты
php artisan route:list | grep deploy

# Должно показать: POST api/deploy

# Тест endpoint
curl -X POST http://localhost/api/deploy \
  -H "Authorization: Bearer 123123123" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'
```

## Полный пример bootstrap/app.php

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

## Полный пример routes/api.php

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LetoceilingCoder\Deploy\DeployController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Deploy endpoint
Route::post('/deploy', [DeployController::class, 'handle']);
```

