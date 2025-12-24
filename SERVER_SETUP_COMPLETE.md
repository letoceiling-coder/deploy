# 🖥️ Полная настройка сервера Beget для Laravel Deploy

Пошаговая инструкция по настройке сервера с нуля.

## 📋 Шаг 1: Подключение к серверу

```bash
# Найдите правильный хост в панели Beget: Хостинг → SSH
ssh letocewh@ПРАВИЛЬНЫЙ_ХОСТ_ИЗ_ПАНЕЛИ

# Переход в директорию проекта
cd ~/letocewh.beget.tech/public_html
```

## 📋 Шаг 2: Проверка окружения

```bash
# PHP версия (должна быть 8.2+)
php -v

# Composer
composer -V

# Node.js и npm
node -v
npm -v

# Git
git --version
```

Если что-то не установлено, см. [SERVER_SETUP.md](SERVER_SETUP.md)

## 📋 Шаг 3: Установка Laravel проекта (если еще нет)

```bash
# Если проект уже есть, пропустите этот шаг
composer create-project laravel/laravel .
```

## 📋 Шаг 4: Установка пакета deploy

```bash
# Добавить репозиторий
composer config repositories.deploy vcs https://github.com/letoceiling-coder/deploy.git

# Установить пакет
composer require letoceiling-coder/deploy:dev-main
```

## 📋 Шаг 5: Настройка .env

```bash
nano .env
```

Добавьте:

```env
# Git репозиторий
GIT_REPOSITORY_URL=https://github.com/letoceiling-coder/avangard.git

# URL сервера для деплоя
DEPLOY_SERVER_URL=https://letocewh.beget.tech/api/deploy

# Токен авторизации
DEPLOY_TOKEN=your-secret-token-here

# Опционально для Beget
COMPOSER_PATH=/home/l/letocewh/bin/composer
PHP_PATH=/usr/local/bin/php8.2
```

## 📋 Шаг 6: Настройка Git

```bash
# Инициализация (если еще не сделано)
git init

# Добавить safe directory
git config --global --add safe.directory /home/l/letocewh/letocewh.beget.tech/public_html

# Настроить пользователя
git config user.name "letocewh"
git config user.email "dev@letocewh.beget.tech"

# Добавить remote
git remote add origin https://github.com/letoceiling-coder/avangard.git

# Проверка
git remote -v
```

## 📋 Шаг 7: Настройка Laravel 12 - bootstrap/app.php

```bash
nano bootstrap/app.php
```

Убедитесь, что есть загрузка API роутов:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // ← Должна быть эта строка
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

## 📋 Шаг 8: Настройка routes/api.php

```bash
nano routes/api.php
```

Содержимое файла:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LetoceilingCoder\Deploy\DeployController;

Route::post('/deploy', [DeployController::class, 'handle']);
```

## 📋 Шаг 9: Очистка кэша

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

## 📋 Шаг 10: Проверка установки

```bash
# 1. Проверить команду deploy
php artisan deploy --help

# 2. Проверить роуты
php artisan route:list | grep deploy
# Должно показать: POST api/deploy

# 3. Проверить все API роуты
php artisan route:list --path=api

# 4. Dry-run тест
php artisan deploy --dry-run
```

## 📋 Шаг 11: Тест endpoint

```bash
# Тест endpoint (замените на ваш реальный домен)
curl -X POST https://letocewh.beget.tech/api/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test","with_seed":false}'
```

Или локально:

```bash
curl -X POST http://localhost/api/deploy \
  -H "Authorization: Bearer 123123123" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'
```

## ✅ Чеклист проверки

- [ ] PHP 8.2+ установлен
- [ ] Composer работает
- [ ] Node.js и npm установлены
- [ ] Git настроен
- [ ] Пакет `letoceiling-coder/deploy` установлен
- [ ] `.env` настроен (GIT_REPOSITORY_URL, DEPLOY_SERVER_URL, DEPLOY_TOKEN)
- [ ] `bootstrap/app.php` загружает `routes/api.php`
- [ ] `routes/api.php` содержит роут `/deploy`
- [ ] Команда `php artisan deploy --help` работает
- [ ] Роут `POST api/deploy` показывается в `php artisan route:list`
- [ ] Endpoint отвечает (не 404)

## 🐛 Решение проблем

### Роут не показывается

```bash
# Проверить bootstrap/app.php
cat bootstrap/app.php | grep "api:"

# Проверить routes/api.php
cat routes/api.php

# Перезапустить обнаружение пакетов
php artisan package:discover
```

### Endpoint возвращает 404

1. Проверьте, что роут зарегистрирован: `php artisan route:list`
2. Используйте правильный URL (не localhost, а реальный домен)
3. Проверьте конфигурацию веб-сервера

### Класс не найден

```bash
# Очистить autoload
composer dump-autoload

# Проверить установку
composer show letoceiling-coder/deploy
```

## 📚 Дополнительная документация

- [SERVER_SETUP.md](SERVER_SETUP.md) - Настройка PHP, Composer, Node.js
- [INSTALLATION.md](INSTALLATION.md) - Установка пакета
- [LARAVEL_12_SETUP.md](LARAVEL_12_SETUP.md) - Настройка для Laravel 12
- [ROUTES_SETUP.md](ROUTES_SETUP.md) - Настройка роутов

