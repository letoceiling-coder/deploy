# 🧪 Тестирование Laravel Deploy Package

Подробное руководство по тестированию пакета на домене `letocewh.beget.tech`.

## 📋 Подготовка к тестированию

### 1. Подключение к серверу

**⚠️ ВАЖНО:** `server.beget.com` - это НЕ правильный хост!

**Правильный способ:**
1. Войдите в панель Beget: https://beget.com/ru
2. Перейдите: **Хостинг → SSH → Включить SSH**
3. Скопируйте **правильный хост** из панели (обычно `serverXX.beget.tech` или `hostXX.beget.pro`)
4. Подключитесь:

```bash
ssh letocewh@ПРАВИЛЬНЫЙ_ХОСТ_ИЗ_ПАНЕЛИ
```

**Пример:**
```bash
ssh letocewh@server12.beget.tech
```

**Альтернатива:** Используйте IP адрес или домен:
```bash
ssh letocewh@letocewh.beget.tech
```

Подробнее см. [SSH_CONNECTION.md](SSH_CONNECTION.md)

### 2. Переход в директорию проекта

```bash
cd ~/www/letocewh.beget.tech
# или
cd /path/to/your/laravel/project
```

### 3. Установка пакета

```bash
composer require letoceiling-coder/deploy
```

## 🔍 Варианты тестирования

### Вариант 1: Автоматическое тестирование окружения

Проверка всех компонентов окружения:

```bash
php tests/TestEnvironment.php
```

**Что проверяется:**
- ✅ PHP версия (8.1+)
- ✅ Git установлен и настроен
- ✅ Composer установлен
- ✅ Node.js и npm (опционально)
- ✅ Laravel проект
- ✅ .env файл и переменные
- ✅ Git репозиторий
- ✅ package.json (если есть)

### Вариант 2: Пошаговое тестирование компонентов

Тестирование каждого компонента отдельно:

```bash
php tests/TestDeploySteps.php
```

**Что тестируется:**
- ✅ Git команды (version, status, branch, commit)
- ✅ Composer команды
- ✅ npm команды (если доступны)
- ✅ Laravel команды (artisan list, deploy --help)
- ✅ Переменные окружения
- ✅ Dry-run режим

### Вариант 3: Полный тест деплоя (dry-run)

Полное тестирование без реального выполнения:

```bash
php tests/TestFullDeploy.php
```

**Что выполняется:**
1. Проверка окружения
2. Dry-run деплой
3. Проверка логов
4. Проверка конфигурации

## 🚀 Пошаговое тестирование вручную

### Шаг 1: Проверка установки пакета

```bash
# Проверка установки
composer show letoceiling-coder/deploy

# Проверка команды
php artisan list | grep deploy
```

**Ожидаемый результат:**
```
deploy  Deploy Laravel project to server
```

### Шаг 2: Проверка help команды

```bash
php artisan deploy --help
```

**Ожидаемый результат:** Список всех доступных флагов и опций.

### Шаг 3: Проверка dry-run режима

```bash
php artisan deploy --dry-run
```

**Ожидаемый результат:**
```
🔍 DRY-RUN mode: No changes will be made
🔍 Validating environment...
  ✓ Git is available
  ✓ npm is available
  ✓ .env file found
📦 Processing Git operations...
  [DRY-RUN] Would stage and commit changes
  [DRY-RUN] Would push to origin/main
🔨 Building assets...
  [DRY-RUN] Would run: npm run build
🌐 Sending deploy request to server...
  [DRY-RUN] Would send POST request to: https://...
```

### Шаг 4: Проверка Git операций

```bash
# Проверка текущей ветки
git rev-parse --abbrev-ref HEAD

# Проверка remotes
git remote -v

# Проверка статуса
git status
```

### Шаг 5: Проверка переменных окружения

```bash
# Проверка .env файла
grep -E "GIT_REPOSITORY_URL|DEPLOY_SERVER_URL|DEPLOY_TOKEN" .env
```

**Ожидаемый результат:**
```
GIT_REPOSITORY_URL=https://github.com/...
DEPLOY_SERVER_URL=https://letocewh.beget.tech/api/deploy
DEPLOY_TOKEN=your-token-here
```

### Шаг 6: Тест с кастомным сообщением

```bash
php artisan deploy --dry-run --message="Test deployment"
```

### Шаг 7: Тест без сборки

```bash
php artisan deploy --dry-run --skip-build
```

### Шаг 8: Проверка логов

```bash
# Просмотр логов
tail -f storage/logs/deploy.log

# Или последние 50 строк
tail -n 50 storage/logs/deploy.log
```

## 🧪 Тестирование реального деплоя

### ⚠️ ВНИМАНИЕ: Тестирование на реальном сервере

Перед реальным деплоем убедитесь:

1. ✅ Все тесты dry-run прошли успешно
2. ✅ Серверный endpoint настроен и работает
3. ✅ Есть backup проекта
4. ✅ Git репозиторий настроен правильно

### Тест 1: Деплой без сборки

```bash
php artisan deploy --skip-build --message="Test: Backend only"
```

### Тест 2: Деплой с проверкой на сервере

1. Выполните деплой:
```bash
php artisan deploy --message="Test deployment"
```

2. Проверьте логи:
```bash
tail -f storage/logs/deploy.log
```

3. Проверьте ответ сервера в логах

4. Проверьте, что код обновился на сервере:
```bash
# На сервере
cd /path/to/project
git log --oneline -1
```

## 🔧 Тестирование на домене letocewh.beget.tech

### Настройка для Beget

Учитывая особенности Beget (см. SERVER_SETUP.md):

1. **PHP 8.2:**
```bash
# Проверка
php -v
# Должно быть: PHP 8.2.28
```

2. **Composer:**
```bash
# Проверка
composer -V
# Должен использовать PHP 8.2
```

3. **Node.js через nvm:**
```bash
# Проверка
node -v
npm -v
```

4. **Настройка .env:**
```env
GIT_REPOSITORY_URL=https://github.com/letoceiling-coder/deploy.git
DEPLOY_SERVER_URL=https://letocewh.beget.tech/api/deploy
DEPLOY_TOKEN=your-secret-token
```

### Создание тестового endpoint на сервере

Создайте файл `routes/api.php`:

```php
Route::post('/deploy', function (Request $request) {
    // Проверка токена
    $token = $request->bearerToken();
    if ($token !== env('DEPLOY_TOKEN')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    // Логирование запроса
    Log::info('Deploy request received', [
        'branch' => $request->input('branch'),
        'version' => $request->input('version'),
    ]);

    // Здесь будет логика деплоя
    // Пока просто возвращаем успех
    return response()->json([
        'success' => true,
        'message' => 'Deploy request received',
        'branch' => $request->input('branch'),
        'version' => $request->input('version'),
    ]);
});
```

### Тестирование endpoint

```bash
# Проверка доступности
curl -X POST https://letocewh.beget.tech/api/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'
```

## 📊 Чеклист тестирования

### Перед тестированием

- [ ] Пакет установлен: `composer show letoceiling-coder/deploy`
- [ ] Команда доступна: `php artisan deploy --help`
- [ ] .env настроен (GIT_REPOSITORY_URL, DEPLOY_SERVER_URL, DEPLOY_TOKEN)
- [ ] Git репозиторий инициализирован
- [ ] Git remote настроен

### Тестирование окружения

- [ ] `php tests/TestEnvironment.php` - все проверки пройдены
- [ ] `php tests/TestDeploySteps.php` - все тесты пройдены
- [ ] `php tests/TestFullDeploy.php` - полный тест успешен

### Тестирование dry-run

- [ ] `php artisan deploy --dry-run` - показывает все шаги
- [ ] `php artisan deploy --dry-run --skip-build` - пропускает сборку
- [ ] `php artisan deploy --dry-run --message="Test"` - кастомное сообщение
- [ ] `php artisan deploy --dry-run --with-seed` - включает seeders

### Тестирование реального деплоя

- [ ] Серверный endpoint доступен
- [ ] Токен авторизации работает
- [ ] Деплой без сборки работает
- [ ] Деплой с сборкой работает
- [ ] Логи записываются корректно
- [ ] Сервер получает и обрабатывает запросы

## 🐛 Отладка проблем

### Проблема: Команда не найдена

```bash
# Очистка кэша
php artisan config:clear
php artisan cache:clear
composer dump-autoload
php artisan package:discover
```

### Проблема: Ошибки в dry-run

```bash
# Проверка логов
tail -f storage/logs/deploy.log

# Проверка конфигурации
php artisan tinker
>>> config('deploy');
```

### Проблема: HTTP запрос не проходит

```bash
# Тест endpoint вручную
curl -X POST https://letocewh.beget.tech/api/deploy \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"branch":"main","version":"test"}'

# Проверка с --insecure (только для теста)
php artisan deploy --insecure --dry-run
```

## 📝 Отчет о тестировании

После тестирования создайте отчет:

```markdown
# Отчет о тестировании Laravel Deploy Package

**Дата:** 2024-12-24
**Домен:** letocewh.beget.tech
**Версия пакета:** 1.0.0

## Результаты

### Окружение
- ✅ PHP 8.2.28
- ✅ Composer 2.9.2
- ✅ Node.js v24.12.0
- ✅ Git настроен

### Тесты
- ✅ TestEnvironment.php: PASSED
- ✅ TestDeploySteps.php: PASSED
- ✅ TestFullDeploy.php: PASSED

### Dry-run
- ✅ Базовый dry-run: OK
- ✅ С флагами: OK

### Реальный деплой
- ✅ Без сборки: OK
- ✅ С сборкой: OK
- ✅ Логи: OK

## Выводы

Пакет работает корректно, готов к использованию.
```

## 🎯 Следующие шаги

После успешного тестирования:

1. Настройте серверный endpoint (см. DEPLOY_SERVER_EXAMPLE.md)
2. Выполните первый реальный деплой
3. Настройте автоматизацию (CI/CD)
4. Документируйте процесс для команды

## 📚 Дополнительная информация

- [INSTALLATION.md](INSTALLATION.md) - Установка
- [USAGE.md](USAGE.md) - Использование
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Решение проблем
- [DEPLOY_SERVER_EXAMPLE.md](DEPLOY_SERVER_EXAMPLE.md) - Серверный endpoint

