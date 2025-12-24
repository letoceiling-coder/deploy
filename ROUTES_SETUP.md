# 🔧 Настройка роутов для Laravel Deploy Package

## Автоматическая установка

Пакет пытается автоматически зарегистрировать роут `/api/deploy`, но в некоторых случаях (особенно в Laravel 12) это может не работать.

## Ручная установка (РЕКОМЕНДУЕТСЯ)

### Шаг 1: Откройте `routes/api.php`

```bash
nano routes/api.php
```

### Шаг 2: Добавьте роут

Добавьте в конец файла (перед закрывающей скобкой, если есть):

```php
use LetoceilingCoder\Deploy\DeployController;

Route::post('/deploy', [DeployController::class, 'handle']);
```

### Шаг 3: Проверка

```bash
# Очистить кэш роутов
php artisan route:clear

# Проверить роуты
php artisan route:list | grep deploy

# Должно показать: POST api/deploy
```

### Шаг 4: Тест endpoint

```bash
curl -X POST http://localhost/api/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'
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

## Проверка работы

После добавления роута:

```bash
# Показать все API роуты
php artisan route:list --path=api

# Должен показать:
# POST api/deploy
```

## Решение проблем

### Роут не появляется в списке

1. Убедитесь, что файл `routes/api.php` существует
2. Проверьте, что роут добавлен правильно
3. Очистите кэш: `php artisan route:clear`

### Endpoint возвращает 404

1. Проверьте, что роут зарегистрирован: `php artisan route:list | grep deploy`
2. Убедитесь, что используете правильный URL: `http://your-domain.com/api/deploy`
3. Проверьте конфигурацию веб-сервера (Apache/Nginx)

### Ошибка "Class not found"

1. Очистите кэш: `php artisan config:clear`
2. Перезапустите обнаружение пакетов: `php artisan package:discover`
3. Убедитесь, что пакет установлен: `composer show letoceiling-coder/deploy`

## Альтернатива: Публикация роутов

Если хотите кастомизировать endpoint:

```bash
php artisan vendor:publish --tag=deploy-routes
```

Это создаст файл `routes/deploy.php`, который можно подключить в `routes/api.php`:

```php
require __DIR__ . '/deploy.php';
```

