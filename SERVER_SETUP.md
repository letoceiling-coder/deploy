# Документация настройки сервера Beget

## Общая информация

- **Пользователь:** letocewh
- **Домашняя директория:** `/home/l/letocewh`
- **Сервер:** Beget (lair)

---

## PHP

### Версия по умолчанию
- **Версия:** PHP 8.2.28
- **Путь к бинарнику:** `/usr/local/bin/php8.2`
- **Полный путь:** `/usr/local/php/cgi/8.2/bin/php`

### Алиас
```bash
alias php='/usr/local/bin/php8.2'
```
Настроен в `~/.bashrc`

### Доступные версии PHP
- PHP 5.6.40: `/usr/bin/php` (старая, не используется)
- PHP 8.1: `/usr/local/bin/php8.1`
- PHP 8.2: `/usr/local/bin/php8.2` ✅ (используется)

### Проверка версии
```bash
php -v
# PHP 8.2.28 (cli) (built: Apr  2 2025 15:03:04) (NTS)
```

---

## Composer

### Версия
- **Версия:** Composer 2.9.2
- **Дата сборки:** 2025-11-19 21:57:25

### Пути
- **Wrapper-скрипт:** `~/bin/composer` → `/home/l/letocewh/bin/composer`
- **Бинарник:** `~/bin/composer.phar` → `/home/l/letocewh/bin/composer.phar`
- **Использует PHP:** `/usr/local/bin/php8.2`

### Структура wrapper-скрипта
```bash
#!/bin/bash
/usr/local/bin/php8.2 ~/bin/composer.phar "$@"
```

### Проверка
```bash
composer -V
# Composer version 2.9.2 2025-11-19 21:57:25
# PHP version 8.2.28 (/usr/local/php/cgi/8.2/bin/php)
```

### Использование
```bash
composer install
composer update
composer require package/name
```

---

## Node.js (через nvm)

### Версия
- **Версия Node.js:** v24.12.0 (LTS)
- **Версия npm:** 11.6.2

### Пути
- **nvm директория:** `~/.nvm` → `/home/l/letocewh/.nvm`
- **Node.js бинарник:** `~/.nvm/versions/node/v24.12.0/bin/node`
- **npm бинарник:** `~/.nvm/versions/node/v24.12.0/bin/npm`
- **npm prefix:** `/home/l/letocewh/.nvm/versions/node/v24.12.0`

### Проверка путей
```bash
which node
# /home/l/letocewh/.nvm/versions/node/v24.12.0/bin/node

which npm
# /home/l/letocewh/.nvm/versions/node/v24.12.0/bin/npm

npm config get prefix
# /home/l/letocewh/.nvm/versions/node/v24.12.0
```

### Проверка версий
```bash
node -v
# v24.12.0

npm -v
# 11.6.2
```

### nvm команды
```bash
nvm list          # Список установленных версий
nvm install --lts # Установка LTS версии
nvm use --lts     # Использование LTS версии
nvm current       # Текущая версия
```

---

## Структура директорий

```
/home/l/letocewh/
├── bin/
│   ├── composer          # Wrapper-скрипт для Composer
│   └── composer.phar     # Бинарник Composer
├── .nvm/                 # nvm директория
│   └── versions/
│       └── node/
│           └── v24.12.0/
│               └── bin/
│                   ├── node
│                   └── npm
├── .bashrc               # Конфигурация shell
├── .pm2/                 # PM2 конфигурация (если используется)
└── www/                  # Веб-проекты (если есть)
    └── project/
```

---

## PATH и переменные окружения

### Настройки в `~/.bashrc`

```bash
# Composer bin в PATH
export PATH="$HOME/bin:$PATH"

# PHP 8.2 по умолчанию
alias php='/usr/local/bin/php8.2'

# nvm загрузка
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
```

### Текущий PATH (после загрузки .bashrc)
```bash
echo $PATH
# Должен содержать:
# /home/l/letocewh/bin (для composer)
# /home/l/letocewh/.nvm/versions/node/v24.12.0/bin (для node/npm)
```

---

## Глобальные npm-пакеты

### Установленные пакеты
- **pm2:** 6.0.14 (Process Manager)

### Установка глобальных пакетов
```bash
npm install -g package-name
# Пакеты устанавливаются в: ~/.nvm/versions/node/v24.12.0/lib/node_modules/
# Бинарники доступны через: ~/.nvm/versions/node/v24.12.0/bin/
```

### Проверка глобальных пакетов
```bash
npm list -g --depth=0
```

---

## Команды для быстрой проверки

### Полная проверка всех инструментов
```bash
php -v && echo "---" && \
composer -V && echo "---" && \
node -v && echo "---" && \
npm -v
```

### Проверка путей
```bash
which php
which composer
which node
which npm
```

### Проверка доступности
```bash
php --version
composer --version
node --version
npm --version
```

---

## Решение проблем

### Composer не видит PHP 8.2
```bash
# Проверить wrapper-скрипт
cat ~/bin/composer

# Должен содержать:
#!/bin/bash
/usr/local/bin/php8.2 ~/bin/composer.phar "$@"
```

### Node.js/npm не доступны
```bash
# Перезагрузить nvm
source ~/.bashrc
# или
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
```

### npm-команды не найдены
```bash
# Проверить PATH
echo $PATH

# Добавить npm bin в PATH (если нужно)
export PATH="$PATH:$(npm config get prefix)/bin"
```

---

## Типичные команды для проекта Laravel

```bash
# Переход в проект
cd ~/www/project

# Установка зависимостей PHP
composer install

# Установка зависимостей Node.js
npm install

# Сборка фронтенда
npm run build
# или
npm run dev

# Запуск миграций
php artisan migrate

# Очистка кэша
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Конфигурационные файлы

### ~/.bashrc
- Содержит настройки PATH, алиасы, загрузку nvm
- Применяется при каждом входе в SSH

### ~/.nvm/
- Конфигурация nvm
- Установленные версии Node.js

### ~/.pm2/
- Конфигурация PM2 (если используется)
- Логи процессов

---

## Версии и даты установки

- **PHP 8.2.28:** Доступен на сервере
- **Composer 2.9.2:** Установлен 24.12.2024
- **Node.js v24.12.0:** Установлен через nvm
- **npm 11.6.2:** Установлен вместе с Node.js
- **pm2 6.0.14:** Установлен глобально

---

## Примечания для ИИ

1. **Всегда используй полные пути** при работе с PHP:
   - `/usr/local/bin/php8.2` вместо просто `php` (если алиас не загружен)

2. **Composer требует wrapper-скрипт** для работы с PHP 8.2:
   - Используй `composer` команду (не `php composer.phar`)

3. **nvm загружается автоматически** через `.bashrc`, но при скриптах может потребоваться явная загрузка

4. **Глобальные npm-пакеты** устанавливаются без sudo, права доступа корректные

5. **Все бинарники в PATH** после загрузки `.bashrc`:
   - `~/bin` для composer
   - `~/.nvm/versions/node/v24.12.0/bin` для node/npm

---

## Быстрые ссылки

- **Домашняя директория:** `/home/l/letocewh`
- **Composer:** `~/bin/composer`
- **Node.js:** `~/.nvm/versions/node/v24.12.0/bin/node`
- **npm:** `~/.nvm/versions/node/v24.12.0/bin/npm`
- **PHP 8.2:** `/usr/local/bin/php8.2`
- **Конфигурация shell:** `~/.bashrc`

