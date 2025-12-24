# 🚀 Использование Laravel Deploy Package

Подробное руководство по использованию команды `php artisan deploy`.

## Базовое использование

### Простой деплой

```bash
php artisan deploy
```

Команда выполнит:
1. ✅ Проверку окружения (git, composer, npm)
2. ✅ Проверку конфигурации (.env)
3. ✅ `git add .` - добавление всех изменений
4. ✅ `git commit` - коммит с автоматическим сообщением
5. ✅ `git push` - отправка в `GIT_REPOSITORY_URL`
6. ✅ `npm run build` - сборка фронтенда (если не пропущено)
7. ✅ HTTP POST запрос на сервер для деплоя

## Флаги команды

### `--message="Custom message"`

Указать кастомное сообщение коммита:

```bash
php artisan deploy --message="Fix user authentication bug"
php artisan deploy --message="Add new feature: user dashboard"
php artisan deploy -m "Hotfix: critical security patch"
```

**Без флага:** Используется автоматическое сообщение: `Deploy: YYYY-MM-DD HH:MM:SS`

### `--skip-build`

Пропустить сборку фронтенда (npm run build):

```bash
php artisan deploy --skip-build
```

**Когда использовать:**
- Деплой только backend изменений
- Фронтенд собирается отдельно
- Нет изменений во фронтенде

### `--dry-run`

Показать все шаги без выполнения:

```bash
php artisan deploy --dry-run
```

**Что покажет:**
- Все команды, которые будут выполнены
- Параметры HTTP запроса
- Не выполнит никаких изменений

**Использование:**
- Проверка конфигурации перед реальным деплоем
- Обучение команды
- Отладка проблем

### `--insecure`

Отключить проверку SSL сертификата:

```bash
php artisan deploy --insecure
```

**⚠️ Внимание:** Используйте только для тестирования или локальных серверов!

**Когда использовать:**
- Локальные серверы без SSL
- Тестовые окружения с самоподписанными сертификатами
- Отладка SSL проблем

### `--with-seed`

Выполнить seeders на сервере после миграций:

```bash
php artisan deploy --with-seed
```

**Что произойдет на сервере:**
```bash
php artisan migrate --force
php artisan db:seed --force
```

**Когда использовать:**
- Первый деплой проекта
- Обновление тестовых данных
- Заполнение справочников

### `--branch=name`

Указать ветку для деплоя:

```bash
php artisan deploy --branch=develop
php artisan deploy --branch=staging
php artisan deploy --branch=main
```

**Без флага:** Используется текущая ветка Git

### `--version=tag`

Указать конкретную версию/тег:

```bash
php artisan deploy --version=v1.2.3
php artisan deploy --version=release-2024-12-24
php artisan deploy --version=abc1234
```

**Без флага:** Используется короткий commit hash (7 символов)

## Комбинирование флагов

### Деплой с кастомным сообщением и без сборки

```bash
php artisan deploy --message="Backend update" --skip-build
```

### Деплой staging ветки с seeders

```bash
php artisan deploy --branch=staging --with-seed --message="Staging deployment with seeds"
```

### Деплой production с версией

```bash
php artisan deploy --branch=main --version=v2.0.0 --message="Production release v2.0.0"
```

### Тестовый запуск (dry-run)

```bash
php artisan deploy --dry-run --message="Test deployment" --branch=develop
```

## Примеры использования

### Ежедневный деплой

```bash
php artisan deploy --message="Daily update $(date +%Y-%m-%d)"
```

### Деплой после исправления бага

```bash
php artisan deploy --message="Fix: User registration validation issue"
```

### Деплой новой фичи

```bash
php artisan deploy --message="Feature: Add email notifications" --with-seed
```

### Деплой только backend

```bash
php artisan deploy --skip-build --message="Backend: Update API endpoints"
```

### Деплой с версией и тегом

```bash
# Сначала создайте тег
git tag -a v1.2.3 -m "Release version 1.2.3"
git push origin v1.2.3

# Затем деплой
php artisan deploy --version=v1.2.3 --message="Release v1.2.3"
```

## Логирование

Все операции логируются в файл:

```
storage/logs/deploy.log
```

### Просмотр логов

```bash
# Последние 50 строк
tail -n 50 storage/logs/deploy.log

# Поиск ошибок
grep ERROR storage/logs/deploy.log

# Логи за сегодня
grep "$(date +%Y-%m-%d)" storage/logs/deploy.log
```

### Формат лога

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

## Интеграция с CI/CD

### GitHub Actions

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install dependencies
        run: composer install
      
      - name: Deploy
        run: php artisan deploy --message="CI/CD: ${{ github.sha }}"
        env:
          GIT_REPOSITORY_URL: ${{ secrets.GIT_REPOSITORY_URL }}
          DEPLOY_SERVER_URL: ${{ secrets.DEPLOY_SERVER_URL }}
          DEPLOY_TOKEN: ${{ secrets.DEPLOY_TOKEN }}
```

### GitLab CI

```yaml
deploy:
  stage: deploy
  script:
    - composer install
    - php artisan deploy --message="GitLab CI: $CI_COMMIT_SHA"
  only:
    - main
  variables:
    GIT_REPOSITORY_URL: $GIT_REPOSITORY_URL
    DEPLOY_SERVER_URL: $DEPLOY_SERVER_URL
    DEPLOY_TOKEN: $DEPLOY_TOKEN
```

## Автоматизация

### Bash скрипт для быстрого деплоя

```bash
#!/bin/bash
# deploy.sh

MESSAGE=${1:-"Auto deploy $(date +%Y-%m-%d\ %H:%M:%S)"}
BRANCH=${2:-$(git rev-parse --abbrev-ref HEAD)}

php artisan deploy --message="$MESSAGE" --branch="$BRANCH"
```

Использование:

```bash
chmod +x deploy.sh
./deploy.sh "My custom message" main
```

### Alias в .bashrc

```bash
alias deploy='php artisan deploy'
alias deploy-dry='php artisan deploy --dry-run'
alias deploy-prod='php artisan deploy --branch=main --message="Production deploy"'
```

## Best Practices

### 1. Всегда используйте dry-run перед первым деплоем

```bash
php artisan deploy --dry-run
```

### 2. Используйте осмысленные сообщения коммитов

```bash
# ❌ Плохо
php artisan deploy --message="update"

# ✅ Хорошо
php artisan deploy --message="Fix: User authentication validation issue #123"
```

### 3. Проверяйте логи после деплоя

```bash
php artisan deploy
tail -f storage/logs/deploy.log
```

### 4. Используйте версии для production

```bash
php artisan deploy --version=v1.2.3 --branch=main
```

### 5. Не используйте --insecure в production

```bash
# ❌ Плохо (production)
php artisan deploy --insecure

# ✅ Хорошо (production)
php artisan deploy
```

## Часто задаваемые вопросы

### Можно ли отменить деплой?

Нет, деплой нельзя отменить через команду. Но вы можете:
- Откатить изменения на сервере через Git
- Выполнить миграции в обратном порядке
- Восстановить из backup

### Что делать, если деплой упал на середине?

1. Проверьте логи: `tail -f storage/logs/deploy.log`
2. Исправьте проблему
3. Повторите деплой: `php artisan deploy`

### Можно ли деплоить без коммита?

Нет, пакет требует коммит перед push. Это гарантирует, что все изменения сохранены.

### Как деплоить только определенные файлы?

Пакет использует `git add .`. Если нужно деплоить только определенные файлы:
1. Сначала закоммитьте нужные файлы вручную
2. Затем выполните: `php artisan deploy --skip-build`

## Дополнительная информация

- [README.md](README.md) - Общая информация
- [INSTALLATION.md](INSTALLATION.md) - Установка
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Решение проблем
- [DEPLOY_SERVER_EXAMPLE.md](DEPLOY_SERVER_EXAMPLE.md) - Серверный endpoint

