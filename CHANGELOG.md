# Changelog

Все значимые изменения в этом проекте будут документироваться в этом файле.

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/),
и этот проект придерживается [Semantic Versioning](https://semver.org/lang/ru/).

## [1.1.0] - 2024-12-24

### Добавлено
- ✅ Поддержка Laravel 12.0
- ✅ Автоматическая установка роутов при установке пакета
- ✅ Endpoint `/api/deploy` доступен автоматически
- ✅ Публикация роутов для кастомизации (`--tag=deploy-routes`)
- ✅ Исправление конфликта с флагом `--version` в Laravel 12 (переименован в `--tag`)
- ✅ Обработка пустого Git репозитория (без коммитов)

### Изменено
- Обновлены требования: `illuminate/support`, `illuminate/console`, `illuminate/http` теперь поддерживают `^12.0`
- Флаг `--version` переименован в `--tag` для совместимости с Laravel 12

## [1.0.0] - 2024-12-24

### Добавлено
- Первый релиз пакета Laravel Deploy
- Команда `php artisan deploy` для автоматического деплоя
- Поддержка всех флагов: `--message`, `--skip-build`, `--dry-run`, `--insecure`, `--with-seed`, `--branch`, `--version`
- GitService для работы с Git (add, commit, push)
- BuildService для сборки фронтенда (npm install, npm run build)
- HttpDeployService для отправки запросов на сервер
- LoggerService для логирования всех операций
- Автоматическая регистрация через Laravel Package Auto-Discovery
- Поддержка Laravel 10.0+ и 11.0+
- Поддержка PHP 8.1+
- Подробная документация (README, INSTALLATION, USAGE, TROUBLESHOOTING)
- Примеры серверного endpoint
- Конфигурационный файл с возможностью публикации
- DTO классы для типизированной передачи данных
- Исключения для обработки ошибок
- Contracts (интерфейсы) для всех сервисов
- Безопасность: маскирование токенов в логах
- SSL проверка (с возможностью отключения через флаг)
- Таймауты для HTTP запросов и npm команд
- Dry-run режим для тестирования без выполнения
- Логирование в `storage/logs/deploy.log`

### Технические детали
- Namespace: `LetoceilingCoder\Deploy`
- Composer package: `letoceiling-coder/deploy`
- Минимальная версия PHP: 8.1
- Минимальная версия Laravel: 10.0
- Зависимости: Guzzle HTTP Client 7.0+

### Документация
- README.md - Общая информация и быстрый старт
- INSTALLATION.md - Подробная инструкция по установке
- USAGE.md - Примеры использования и best practices
- TROUBLESHOOTING.md - Решение типичных проблем
- DEPLOY_SERVER_EXAMPLE.md - Пример реализации серверного endpoint
- SERVER_SETUP.md - Настройка сервера Beget

---

## [Unreleased]

### Планируется
- Поддержка множественных серверов
- Webhook уведомления
- Rollback функциональность
- Интеграция с популярными CI/CD системами
- Поддержка Docker
- Метрики и мониторинг
- Тесты (PHPUnit)

---

[1.0.0]: https://github.com/letoceiling-coder/deploy/releases/tag/v1.0.0

