<?php

/**
 * Полный тест деплоя (без реального выполнения)
 * 
 * Использование:
 * php tests/TestFullDeploy.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Process;

echo "🚀 Полный тест деплоя (dry-run)\n";
echo str_repeat("=", 60) . "\n\n";

// Шаг 1: Проверка окружения
echo "Шаг 1: Проверка окружения...\n";
$envCheck = Process::run('php tests/TestEnvironment.php');
if ($envCheck->successful()) {
    echo "✅ Окружение готово\n\n";
} else {
    echo "❌ Окружение не готово. Исправьте ошибки.\n";
    exit(1);
}

// Шаг 2: Dry-run деплой
echo "Шаг 2: Выполнение dry-run деплоя...\n";
echo str_repeat("-", 60) . "\n";

$deployDryRun = Process::run('php artisan deploy --dry-run --message="Test deployment" 2>&1');

echo $deployDryRun->output();

if ($deployDryRun->successful()) {
    echo "\n✅ Dry-run выполнен успешно\n\n";
} else {
    echo "\n❌ Dry-run провален\n";
    echo "Ошибка: " . $deployDryRun->errorOutput() . "\n";
    exit(1);
}

// Шаг 3: Проверка логов
echo "Шаг 3: Проверка логов...\n";
$logFile = storage_path('logs/deploy.log');
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    $logLines = count(file($logFile));
    echo "✅ Лог файл существует\n";
    echo "   Размер: " . number_format($logSize / 1024, 2) . " KB\n";
    echo "   Строк: {$logLines}\n";
    
    // Показываем последние 10 строк
    $lastLines = array_slice(file($logFile), -10);
    echo "\n   Последние записи:\n";
    foreach ($lastLines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "⚠️  Лог файл не найден (может быть создан при реальном деплое)\n";
}
echo "\n";

// Шаг 4: Проверка конфигурации
echo "Шаг 4: Проверка конфигурации пакета...\n";
if (function_exists('config')) {
    $config = config('deploy');
    if ($config) {
        echo "✅ Конфигурация загружена\n";
        echo "   Git remote: " . ($config['git']['remote'] ?? 'N/A') . "\n";
        echo "   Server URL: " . ($config['server']['url'] ?? 'N/A') . "\n";
        echo "   Timeout: " . ($config['server']['timeout'] ?? 'N/A') . "s\n";
    } else {
        echo "⚠️  Конфигурация не найдена (опционально)\n";
    }
} else {
    echo "⚠️  Функция config() недоступна (может быть нормально вне Laravel)\n";
}
echo "\n";

// Итоги
echo str_repeat("=", 60) . "\n";
echo "✅ ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ\n";
echo str_repeat("=", 60) . "\n\n";

echo "Пакет готов к использованию!\n\n";
echo "Для реального деплоя выполните:\n";
echo "   php artisan deploy --message=\"Your commit message\"\n\n";
echo "Или с дополнительными опциями:\n";
echo "   php artisan deploy --message=\"Test\" --skip-build\n";
echo "   php artisan deploy --message=\"Test\" --with-seed\n";
echo "   php artisan deploy --message=\"Test\" --branch=develop\n";

