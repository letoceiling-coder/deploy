# 🔧 Решение проблем Laravel Deploy Package

Руководство по решению типичных проблем при использовании пакета.

## Общие проблемы

### Ошибка: "Git is not available"

**Симптомы:**
```
❌ Configuration error: Git is not available. Please install git.
```

**Решение:**

1. Проверьте, установлен ли Git:
```bash
git --version
```

2. Если не установлен, установите:

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install git
```

**macOS:**
```bash
brew install git
```

**Windows:**
Скачайте с [git-scm.com](https://git-scm.com/download/win)

3. Проверьте PATH:
```bash
which git
```

---

### Ошибка: "Git repository is not initialized"

**Симптомы:**
```
❌ Configuration error: Git repository is not initialized. Run: git init
```

**Решение:**

1. Инициализируйте репозиторий:
```bash
git init
```

2. Добавьте remote:
```bash
git remote add origin https://github.com/your-username/your-repo.git
```

3. Проверьте:
```bash
git remote -v
```

---

### Ошибка: "npm is not available"

**Симптомы:**
```
❌ Configuration error: npm is not available. Please install Node.js and npm.
```

**Решение:**

1. Если не используете фронтенд, используйте флаг:
```bash
php artisan deploy --skip-build
```

2. Если нужен npm, установите Node.js:

**Через nvm (рекомендуется):**
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
source ~/.bashrc
nvm install --lts
```

**Прямая установка:**
- Ubuntu/Debian: `sudo apt-get install nodejs npm`
- macOS: `brew install node`
- Windows: Скачайте с [nodejs.org](https://nodejs.org/)

3. Проверьте:
```bash
node -v
npm -v
```

---

### Ошибка: "DEPLOY_TOKEN is not set"

**Симптомы:**
```
❌ Configuration error: Missing required environment variables: DEPLOY_TOKEN
```

**Решение:**

1. Откройте `.env` файл:
```bash
nano .env
```

2. Добавьте переменную:
```env
DEPLOY_TOKEN=your-secret-token-here
```

3. Перезапустите приложение (если нужно):
```bash
php artisan config:clear
```

---

### Ошибка: "DEPLOY_SERVER_URL is not set"

**Симптомы:**
```
❌ Configuration error: Missing required environment variables: DEPLOY_SERVER_URL
```

**Решение:**

1. Добавьте в `.env`:
```env
DEPLOY_SERVER_URL=https://your-server.com/api/deploy
```

2. Убедитесь, что URL валидный:
```bash
php artisan tinker
>>> filter_var('https://your-server.com/api/deploy', FILTER_VALIDATE_URL);
```

---

### Ошибка: "GIT_REPOSITORY_URL is not set"

**Симптомы:**
```
❌ Configuration error: Missing required environment variables: GIT_REPOSITORY_URL
```

**Решение:**

1. Добавьте в `.env`:
```env
GIT_REPOSITORY_URL=https://github.com/your-username/your-repo.git
```

2. Проверьте, что remote настроен:
```bash
git remote -v
```

---

### Ошибка: "Failed to push"

**Симптомы:**
```
❌ Git error: Failed to push: error: failed to push some refs
```

**Решение:**

1. Проверьте права доступа к репозиторию
2. Убедитесь, что remote URL правильный:
```bash
git remote get-url origin
```

3. Попробуйте pull перед push:
```bash
git pull origin main
php artisan deploy
```

4. Проверьте авторизацию:
```bash
git push origin main
```

---

### Ошибка: "npm run build failed"

**Симптомы:**
```
❌ Build error: npm run build failed: ...
```

**Решение:**

1. Проверьте package.json:
```bash
cat package.json
```

2. Убедитесь, что есть скрипт build:
```json
{
  "scripts": {
    "build": "vite build"
  }
}
```

3. Попробуйте собрать вручную:
```bash
npm install
npm run build
```

4. Если проблема не решается, пропустите сборку:
```bash
php artisan deploy --skip-build
```

---

### Ошибка: "HTTP request failed"

**Симптомы:**
```
❌ Deploy request error: HTTP request failed: cURL error 60: SSL certificate problem
```

**Решение:**

1. **Для тестирования** используйте `--insecure`:
```bash
php artisan deploy --insecure
```

2. **Для production** исправьте SSL сертификат на сервере

3. Проверьте доступность сервера:
```bash
curl https://your-server.com/api/deploy
```

---

### Ошибка: "Unauthorized"

**Симптомы:**
```
❌ Deploy request error: Deploy request failed with status 401: Unauthorized
```

**Решение:**

1. Проверьте токен в `.env`:
```bash
grep DEPLOY_TOKEN .env
```

2. Убедитесь, что токен правильный на сервере

3. Проверьте формат токена (без пробелов, кавычек)

---

### Ошибка: "Command not found"

**Симптомы:**
```
Command "deploy" is not defined.
```

**Решение:**

1. Очистите кэш:
```bash
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

2. Перезапустите обнаружение пакетов:
```bash
php artisan package:discover
```

3. Проверьте, что пакет установлен:
```bash
composer show letoceiling-coder/deploy
```

4. Убедитесь, что Service Provider зарегистрирован в `config/app.php` (если auto-discovery не работает)

---

### Ошибка: "Class not found"

**Симптомы:**
```
Class 'LetoceilingCoder\Deploy\...' not found
```

**Решение:**

1. Обновите autoload:
```bash
composer dump-autoload
```

2. Очистите кэш:
```bash
php artisan config:clear
php artisan cache:clear
```

3. Переустановите пакет:
```bash
composer remove letoceiling-coder/deploy
composer require letoceiling-coder/deploy
```

---

## Проблемы с логированием

### Логи не создаются

**Решение:**

1. Проверьте права на директорию:
```bash
chmod -R 775 storage/logs
```

2. Убедитесь, что директория существует:
```bash
mkdir -p storage/logs
```

3. Проверьте настройки в `.env`:
```env
DEPLOY_LOGGING=true
```

---

### Логи слишком большие

**Решение:**

1. Очистите старые логи:
```bash
> storage/logs/deploy.log
```

2. Настройте ротацию логов (через logrotate или cron)

---

## Проблемы с производительностью

### Деплой выполняется слишком долго

**Решение:**

1. Используйте `--skip-build` если фронтенд не изменился:
```bash
php artisan deploy --skip-build
```

2. Увеличьте таймауты в `.env`:
```env
DEPLOY_TIMEOUT=600
NPM_TIMEOUT=1200
```

3. Оптимизируйте сборку фронтенда (используйте кэш npm)

---

## Проблемы с сервером

### Сервер не получает запросы

**Решение:**

1. Проверьте доступность endpoint:
```bash
curl -X POST https://your-server.com/api/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'
```

2. Проверьте логи сервера

3. Убедитесь, что сервер принимает POST запросы

---

### Сервер возвращает ошибку 500

**Решение:**

1. Проверьте логи сервера
2. Убедитесь, что все зависимости установлены на сервере
3. Проверьте права доступа к файлам
4. Убедитесь, что PHP версия совместима

---

## Отладка

### Включение подробного логирования

Добавьте в `.env`:
```env
APP_DEBUG=true
DEPLOY_LOGGING=true
```

### Просмотр всех шагов (dry-run)

```bash
php artisan deploy --dry-run
```

### Проверка конфигурации

```bash
php artisan tinker
>>> config('deploy');
>>> env('DEPLOY_SERVER_URL');
```

### Проверка Git статуса

```bash
git status
git log --oneline -5
git remote -v
```

---

## Получение помощи

Если проблема не решена:

1. Проверьте логи: `storage/logs/deploy.log`
2. Выполните dry-run: `php artisan deploy --dry-run`
3. Создайте issue на GitHub: [github.com/letoceiling-coder/deploy/issues](https://github.com/letoceiling-coder/deploy/issues)

При создании issue укажите:
- Версию Laravel
- Версию PHP
- Полный текст ошибки
- Логи из `storage/logs/deploy.log`
- Результат `php artisan deploy --dry-run`

---

## Полезные команды

```bash
# Проверка окружения
git --version
composer --version
npm --version
php -v

# Проверка конфигурации
php artisan config:show deploy
php artisan deploy --dry-run

# Очистка кэша
php artisan config:clear
php artisan cache:clear
composer dump-autoload

# Просмотр логов
tail -f storage/logs/deploy.log
```

