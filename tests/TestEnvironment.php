<?php

/**
 * Скрипт для проверки окружения перед тестированием пакета
 * 
 * Использование:
 * php tests/TestEnvironment.php
 */

echo "🔍 Проверка окружения для Laravel Deploy Package\n";
echo str_repeat("=", 60) . "\n\n";

$errors = [];
$warnings = [];

// 1. Проверка PHP
echo "1. Проверка PHP...\n";
$phpVersion = phpversion();
echo "   Версия PHP: {$phpVersion}\n";
if (version_compare($phpVersion, '8.1.0', '<')) {
    $errors[] = "PHP версия должна быть 8.1 или выше";
} else {
    echo "   ✅ PHP версия подходит\n";
}
echo "\n";

// 2. Проверка Git
echo "2. Проверка Git...\n";
$gitVersion = shell_exec('git --version 2>&1');
if ($gitVersion) {
    echo "   {$gitVersion}";
    echo "   ✅ Git установлен\n";
} else {
    $errors[] = "Git не установлен";
    echo "   ❌ Git не найден\n";
}
echo "\n";

// 3. Проверка Composer
echo "3. Проверка Composer...\n";
$composerVersion = shell_exec('composer --version 2>&1');
if ($composerVersion) {
    echo "   {$composerVersion}";
    echo "   ✅ Composer установлен\n";
} else {
    $errors[] = "Composer не установлен";
    echo "   ❌ Composer не найден\n";
}
echo "\n";

// 4. Проверка Node.js и npm
echo "4. Проверка Node.js и npm...\n";
$nodeVersion = shell_exec('node --version 2>&1');
if ($nodeVersion) {
    echo "   Node.js: " . trim($nodeVersion) . "\n";
    echo "   ✅ Node.js установлен\n";
} else {
    $warnings[] = "Node.js не установлен (опционально, если используется --skip-build)";
    echo "   ⚠️  Node.js не найден\n";
}

$npmVersion = shell_exec('npm --version 2>&1');
if ($npmVersion) {
    echo "   npm: " . trim($npmVersion) . "\n";
    echo "   ✅ npm установлен\n";
} else {
    $warnings[] = "npm не установлен (опционально, если используется --skip-build)";
    echo "   ⚠️  npm не найден\n";
}
echo "\n";

// 5. Проверка Laravel
echo "5. Проверка Laravel...\n";
if (file_exists('artisan')) {
    $laravelVersion = shell_exec('php artisan --version 2>&1');
    echo "   {$laravelVersion}";
    echo "   ✅ Laravel найден\n";
} else {
    $errors[] = "Laravel не найден (нет файла artisan)";
    echo "   ❌ Laravel не найден\n";
}
echo "\n";

// 6. Проверка .env
echo "6. Проверка .env файла...\n";
if (file_exists('.env')) {
    echo "   ✅ .env файл существует\n";
    
    $envContent = file_get_contents('.env');
    $requiredVars = [
        'GIT_REPOSITORY_URL',
        'DEPLOY_SERVER_URL',
        'DEPLOY_TOKEN'
    ];
    
    foreach ($requiredVars as $var) {
        if (preg_match("/^{$var}=/m", $envContent)) {
            echo "   ✅ {$var} установлен\n";
        } else {
            $errors[] = "Переменная {$var} не найдена в .env";
            echo "   ❌ {$var} не найден\n";
        }
    }
} else {
    $errors[] = ".env файл не найден";
    echo "   ❌ .env файл не найден\n";
}
echo "\n";

// 7. Проверка Git репозитория
echo "7. Проверка Git репозитория...\n";
if (is_dir('.git')) {
    echo "   ✅ Git репозиторий инициализирован\n";
    
    $currentBranch = shell_exec('git rev-parse --abbrev-ref HEAD 2>&1');
    echo "   Текущая ветка: " . trim($currentBranch) . "\n";
    
    $remotes = shell_exec('git remote -v 2>&1');
    if ($remotes) {
        echo "   Remotes:\n";
        echo "   {$remotes}\n";
    } else {
        $warnings[] = "Git remotes не настроены";
        echo "   ⚠️  Git remotes не настроены\n";
    }
} else {
    $errors[] = "Git репозиторий не инициализирован";
    echo "   ❌ Git репозиторий не найден\n";
}
echo "\n";

// 8. Проверка package.json (опционально)
echo "8. Проверка package.json...\n";
if (file_exists('package.json')) {
    echo "   ✅ package.json найден\n";
    $packageJson = json_decode(file_get_contents('package.json'), true);
    if (isset($packageJson['scripts']['build'])) {
        echo "   ✅ Скрипт 'build' найден\n";
    } else {
        $warnings[] = "Скрипт 'build' не найден в package.json";
        echo "   ⚠️  Скрипт 'build' не найден\n";
    }
} else {
    $warnings[] = "package.json не найден (опционально, если используется --skip-build)";
    echo "   ⚠️  package.json не найден\n";
}
echo "\n";

// Итоги
echo str_repeat("=", 60) . "\n";
echo "📊 ИТОГИ ПРОВЕРКИ\n";
echo str_repeat("=", 60) . "\n\n";

if (empty($errors)) {
    echo "✅ Все критические проверки пройдены!\n";
    echo "Пакет готов к использованию.\n\n";
    
    if (!empty($warnings)) {
        echo "⚠️  Предупреждения:\n";
        foreach ($warnings as $warning) {
            echo "   - {$warning}\n";
        }
        echo "\n";
    }
    
    echo "Следующий шаг: выполните тестовый деплой:\n";
    echo "   php artisan deploy --dry-run\n";
    exit(0);
} else {
    echo "❌ Найдены критические ошибки:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n";
    
    if (!empty($warnings)) {
        echo "⚠️  Предупреждения:\n";
        foreach ($warnings as $warning) {
            echo "   - {$warning}\n";
        }
        echo "\n";
    }
    
    echo "Исправьте ошибки перед использованием пакета.\n";
    exit(1);
}

