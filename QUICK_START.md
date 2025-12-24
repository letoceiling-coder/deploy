# 🚀 Быстрый старт на сервере Beget

Пошаговая инструкция для установки и тестирования пакета на сервере.

## Шаг 1: Найти директорию проекта

```bash
# Проверка текущей директории
pwd

# Поиск Laravel проектов
find ~ -name "artisan" -type f 2>/dev/null
find ~ -name "composer.json" -type f 2>/dev/null

# Или проверка стандартных директорий Beget
ls -la ~/www/
ls -la ~/domains/
ls -la ~/public_html/
```

## Шаг 2: Переход в проект или создание нового

### Вариант A: Если проект уже существует

```bash
cd /path/to/your/laravel/project
```

### Вариант B: Создание нового Laravel проекта для тестирования

```bash
# Создание директории
mkdir -p ~/test-deploy
cd ~/test-deploy

# Создание нового Laravel проекта
composer create-project laravel/laravel .

# Или если уже есть composer.json, просто установите зависимости
composer install
```

## Шаг 3: Установка пакета из GitHub

Пакет еще не опубликован в Packagist, поэтому устанавливаем из GitHub:

### Способ 1: Добавить репозиторий в composer.json

```bash
# Если composer.json существует, откройте его
nano composer.json
```

Добавьте в `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/letoceiling-coder/deploy.git"
        }
    ],
    "require": {
        "letoceiling-coder/deploy": "dev-main"
    }
}
```

Затем:

```bash
composer update
```

### Способ 2: Прямая установка с указанием репозитория

```bash
composer require letoceiling-coder/deploy:dev-main --repository='{"type":"vcs","url":"https://github.com/letoceiling-coder/deploy.git"}'
```

### Способ 3: Клонирование и установка локально

```bash
# Клонирование пакета
cd ~
git clone https://github.com/letoceiling-coder/deploy.git
cd deploy
composer install

# Затем в вашем Laravel проекте добавьте в composer.json:
# "repositories": [{"type": "path", "url": "../deploy"}]
# "require": {"letoceiling-coder/deploy": "*"}
```

## Шаг 4: Настройка .env

```bash
# Открыть .env
nano .env
```

Добавьте:

```env
GIT_REPOSITORY_URL=https://github.com/your-username/your-repo.git
DEPLOY_SERVER_URL=https://letocewh.beget.tech/api/deploy
DEPLOY_TOKEN=your-secret-token-here
```

## Шаг 5: Проверка установки

```bash
# Проверка команды
php artisan list | grep deploy

# Проверка help
php artisan deploy --help

# Dry-run тест
php artisan deploy --dry-run
```

## Шаг 6: Запуск тестов

```bash
# Если тесты скопированы в проект
php tests/TestEnvironment.php
php tests/TestDeploySteps.php
```

---

## 🔧 Если проект не найден

### Создание минимального тестового проекта

```bash
# Создание директории
mkdir -p ~/test-deploy-package
cd ~/test-deploy-package

# Создание composer.json
cat > composer.json << 'EOF'
{
    "name": "test/deploy-test",
    "type": "project",
    "require": {
        "php": "^8.1",
        "illuminate/support": "^10.0|^11.0",
        "illuminate/console": "^10.0|^11.0",
        "illuminate/http": "^10.0|^11.0",
        "guzzlehttp/guzzle": "^7.0"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/letoceiling-coder/deploy.git"
        }
    ],
    "require": {
        "letoceiling-coder/deploy": "dev-main"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
EOF

# Установка
composer install
```

---

## 📝 Полная последовательность команд

```bash
# 1. Найти или создать проект
find ~ -name "artisan" -type f 2>/dev/null | head -1
# Или создать новый:
mkdir -p ~/test-deploy && cd ~/test-deploy

# 2. Если это новый проект, создать composer.json
# (см. выше)

# 3. Добавить репозиторий и установить пакет
composer require letoceiling-coder/deploy:dev-main \
  --repository='{"type":"vcs","url":"https://github.com/letoceiling-coder/deploy.git"}'

# 4. Настроить .env
nano .env
# Добавить переменные (см. выше)

# 5. Проверить
php artisan deploy --dry-run
```

