# Laravel Deploy Package

[![Latest Version](https://img.shields.io/github/v/release/letoceiling-coder/deploy)](https://github.com/letoceiling-coder/deploy)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10.0%2B-red.svg)](https://laravel.com)

Автоматический деплой Laravel-проектов на сервер через Git и HTTP API.

## ✨ Особенности

- 🚀 **Простая установка** - один команда через Composer
- 🔄 **Автоматический деплой** - Git commit, push и HTTP запрос на сервер
- 🛠️ **Гибкая настройка** - множество флагов для различных сценариев
- 📝 **Подробное логирование** - все операции записываются в лог
- 🔒 **Безопасность** - токены маскируются, SSL проверка
- 🧪 **Dry-run режим** - тестирование без выполнения
- 📦 **Автоматическая регистрация** - Laravel Package Auto-Discovery

## 📦 Установка

```bash
composer require letoceiling-coder/deploy
```

Пакет автоматически регистрируется. Никаких дополнительных действий не требуется!

## ⚙️ Настройка

### 1. Переменные окружения (.env)

Добавьте следующие переменные в ваш `.env` файл:

```env
# Git репозиторий
GIT_REPOSITORY_URL=https://github.com/username/repository.git

# URL сервера для деплоя
DEPLOY_SERVER_URL=https://your-server.com/api/deploy

# Токен авторизации
DEPLOY_TOKEN=your-secret-token-here
```

### 2. Публикация конфигурации (опционально)

```bash
php artisan vendor:publish --tag=deploy-config
```

## 🚀 Использование

### Базовое использование

```bash
php artisan deploy
```

Команда выполнит:
1. ✅ Проверку окружения (git, composer, npm)
2. ✅ Проверку конфигурации (.env)
3. ✅ `git add .`
4. ✅ `git commit` (с автоматическим сообщением)
5. ✅ `git push` в `GIT_REPOSITORY_URL`
6. ✅ `npm run build` (если не пропущено)
7. ✅ HTTP POST запрос на сервер для деплоя

### Флаги команды

#### `--message="Custom message"`
Кастомное сообщение коммита:

```bash
php artisan deploy --message="Fix user authentication bug"
```

#### `--skip-build`
Пропустить сборку фронтенда:

```bash
php artisan deploy --skip-build
```

#### `--dry-run`
Показать все шаги без выполнения:

```bash
php artisan deploy --dry-run
```

#### `--insecure`
Отключить проверку SSL сертификата:

```bash
php artisan deploy --insecure
```

#### `--with-seed`
Выполнить seeders на сервере:

```bash
php artisan deploy --with-seed
```

#### `--branch=main`
Указать ветку для деплоя:

```bash
php artisan deploy --branch=develop
```

#### `--version=v1.2.3`
Указать конкретную версию/тег:

```bash
php artisan deploy --version=v1.2.3
```

### Комбинирование флагов

```bash
php artisan deploy \
  --message="Production release" \
  --branch=main \
  --version=v1.0.0 \
  --with-seed \
  --skip-build
```

## 📋 Логирование

Все операции логируются в файл:

```
storage/logs/deploy.log
```

Каждый шаг содержит:
- Временную метку
- Уровень логирования (INFO, ERROR, WARNING, DEBUG)
- Сообщение
- Контекст (дополнительные данные)

Пример лога:

```
[2024-12-24 12:30:45] [INFO] [STEP: DEPLOY] Deployment started
[2024-12-24 12:30:45] [INFO] [STEP: VALIDATE] Validating environment
[2024-12-24 12:30:46] [INFO] [STEP: GIT] Staging all changes
[2024-12-24 12:30:47] [INFO] [STEP: GIT] Committing changes: Deploy: 2024-12-24 12:30:47
[2024-12-24 12:30:48] [INFO] [STEP: GIT] Pushing to origin/main
[2024-12-24 12:30:50] [INFO] [STEP: BUILD] Running npm run build
[2024-12-24 12:31:15] [INFO] [STEP: HTTP] Sending deploy request to: https://server.com/api/deploy
[2024-12-24 12:31:16] [INFO] [STEP: DEPLOY] Deployment completed successfully
```

## 🖥️ Настройка сервера

Сервер должен принимать POST запросы на `DEPLOY_SERVER_URL` со следующими параметрами:

### Запрос

**Headers:**
```
Authorization: Bearer {DEPLOY_TOKEN}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "branch": "main",
  "version": "abc1234",
  "with_seed": false
}
```

### Ожидаемое поведение сервера

Сервер должен выполнить следующие шаги:

1. ✅ Проверить токен авторизации
2. ✅ `git pull origin {branch}`
3. ✅ `composer install --no-dev`
4. ✅ `npm install`
5. ✅ `npm run build`
6. ✅ `php artisan migrate --force`
7. ✅ `php artisan db:seed` (если `with_seed: true`)
8. ✅ `php artisan optimize:clear`

### Пример ответа сервера

**Успешный ответ:**
```json
{
  "success": true,
  "message": "Deployment completed",
  "steps": {
    "git_pull": "success",
    "composer_install": "success",
    "npm_install": "success",
    "npm_build": "success",
    "migrate": "success",
    "optimize": "success"
  }
}
```

**Ошибка:**
```json
{
  "success": false,
  "message": "Deployment failed",
  "error": "Migration failed: ...",
  "step": "migrate"
}
```

## 🔒 Безопасность

- ✅ Токен не логируется полностью (маскируется в логах)
- ✅ SSL проверка включена по умолчанию (отключается только с `--insecure`)
- ✅ Таймауты для HTTP запросов (300 секунд)
- ✅ Проверка exit-кодов всех shell-команд

## 🧪 Dry-Run режим

При использовании `--dry-run` команда покажет все шаги без выполнения:

```
🔍 DRY-RUN mode: No changes will be made
📦 Processing Git operations...
  [DRY-RUN] Would stage and commit changes
  [DRY-RUN] Would push to origin/main
🔨 Building assets...
  [DRY-RUN] Would run: npm run build
🌐 Sending deploy request to server...
  [DRY-RUN] Would send POST request to: https://server.com/api/deploy
```

## 📁 Структура пакета

```
src/
├── Commands/
│   └── DeployCommand.php          # Главная команда
├── Services/
│   ├── GitService.php              # Работа с Git
│   ├── BuildService.php            # Сборка фронтенда
│   ├── HttpDeployService.php       # HTTP запросы
│   └── LoggerService.php           # Логирование
├── DTO/
│   ├── DeployOptionsDTO.php        # Опции команды
│   ├── DeployRequestDTO.php        # HTTP запрос
│   └── GitCommitDTO.php           # Git коммит
├── Exceptions/
│   ├── DeployException.php         # Базовое исключение
│   ├── GitException.php            # Ошибки Git
│   ├── BuildException.php          # Ошибки сборки
│   ├── HttpDeployException.php     # Ошибки HTTP
│   └── ConfigurationException.php  # Ошибки конфигурации
├── Contracts/
│   ├── GitServiceInterface.php
│   ├── BuildServiceInterface.php
│   ├── HttpDeployServiceInterface.php
│   └── LoggerServiceInterface.php
└── DeployServiceProvider.php       # Service Provider
```

## 🛠️ Разработка

### Требования

- PHP 8.1+
- Laravel 10.0+ или 11.0+
- Git
- Node.js и npm (для сборки фронтенда)

### Установка для разработки

```bash
git clone https://github.com/letoceiling-coder/deploy.git
cd deploy
composer install
```

### Тестирование

```bash
php artisan deploy --dry-run
```

## 📝 Примеры использования

### Ежедневный деплой

```bash
php artisan deploy --message="Daily update"
```

### Деплой с миграциями и сидерами

```bash
php artisan deploy --with-seed --message="Database update"
```

### Деплой конкретной ветки

```bash
php artisan deploy --branch=staging --message="Staging deployment"
```

### Деплой с версией

```bash
php artisan deploy --version=v2.0.0 --message="Release v2.0.0"
```

### Деплой без сборки фронтенда

```bash
php artisan deploy --skip-build --message="Backend only update"
```

## 🐛 Решение проблем

### Ошибка: "Git is not available"

Установите Git:
```bash
# Ubuntu/Debian
sudo apt-get install git

# macOS
brew install git
```

### Ошибка: "npm is not available"

Установите Node.js и npm. См. документацию в `SERVER_SETUP.md`.

### Ошибка: "DEPLOY_TOKEN is not set"

Добавьте переменную в `.env`:
```env
DEPLOY_TOKEN=your-token-here
```

### Ошибка: "SSL verification failed"

Используйте флаг `--insecure` (только для тестирования):
```bash
php artisan deploy --insecure
```

## 📚 Документация

- [INSTALLATION.md](INSTALLATION.md) - Подробная инструкция по установке
- [USAGE.md](USAGE.md) - Примеры использования и best practices
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Решение типичных проблем
- [DEPLOY_SERVER_EXAMPLE.md](DEPLOY_SERVER_EXAMPLE.md) - Пример серверного endpoint
- [SERVER_SETUP.md](SERVER_SETUP.md) - Настройка сервера Beget
- [CHANGELOG.md](CHANGELOG.md) - История изменений

## 🤝 Поддержка

Если у вас возникли проблемы:

1. Проверьте [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. Создайте [Issue](https://github.com/letoceiling-coder/deploy/issues)

## 📄 Лицензия

MIT License. См. [LICENSE](LICENSE) файл для деталей.

## 👤 Автор

**Letoceiling Coder**

- GitHub: [@letoceiling-coder](https://github.com/letoceiling-coder)
- Email: dev@letocewh.beget.tech

## 🙏 Благодарности

Спасибо всем, кто использует и улучшает этот пакет!

## 📈 Статистика

- Версия: 1.0.0
- PHP: 8.1+
- Laravel: 10.0+ / 11.0+

