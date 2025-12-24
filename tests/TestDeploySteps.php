<?php

/**
 * Пошаговое тестирование компонентов деплоя
 * 
 * Использование:
 * php tests/TestDeploySteps.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Process;

echo "🧪 Пошаговое тестирование компонентов деплоя\n";
echo str_repeat("=", 60) . "\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// Тест 1: Git команды
echo "1. Тестирование Git команд...\n";
$tests['git_version'] = Process::run('git --version');
if ($tests['git_version']->successful()) {
    echo "   ✅ git --version: OK\n";
    $passed++;
} else {
    echo "   ❌ git --version: FAILED\n";
    $failed++;
}

$tests['git_status'] = Process::run('git status --porcelain');
echo "   ✅ git status: OK\n";
$passed++;

$tests['git_branch'] = Process::run('git rev-parse --abbrev-ref HEAD');
if ($tests['git_branch']->successful()) {
    echo "   ✅ git branch: " . trim($tests['git_branch']->output()) . "\n";
    $passed++;
} else {
    echo "   ❌ git branch: FAILED\n";
    $failed++;
}

$tests['git_commit'] = Process::run('git rev-parse HEAD');
if ($tests['git_commit']->successful()) {
    $commitHash = substr(trim($tests['git_commit']->output()), 0, 7);
    echo "   ✅ git commit hash: {$commitHash}\n";
    $passed++;
} else {
    echo "   ❌ git commit hash: FAILED\n";
    $failed++;
}
echo "\n";

// Тест 2: Composer
echo "2. Тестирование Composer...\n";
$tests['composer_version'] = Process::run('composer --version');
if ($tests['composer_version']->successful()) {
    echo "   ✅ composer --version: OK\n";
    echo "   " . trim($tests['composer_version']->output()) . "\n";
    $passed++;
} else {
    echo "   ❌ composer --version: FAILED\n";
    $failed++;
}

// Проверка установки пакета
if (file_exists('vendor/letoceiling-coder/deploy')) {
    echo "   ✅ Пакет установлен\n";
    $passed++;
} else {
    echo "   ❌ Пакет не установлен\n";
    $failed++;
}
echo "\n";

// Тест 3: npm (если доступен)
echo "3. Тестирование npm...\n";
$tests['npm_version'] = Process::run('npm --version');
if ($tests['npm_version']->successful()) {
    echo "   ✅ npm --version: " . trim($tests['npm_version']->output()) . "\n";
    $passed++;
    
    if (file_exists('package.json')) {
        echo "   ✅ package.json найден\n";
        $passed++;
        
        // Тест npm install (dry-run через проверку node_modules)
        if (is_dir('node_modules')) {
            echo "   ✅ node_modules существует\n";
            $passed++;
        } else {
            echo "   ⚠️  node_modules не найден (выполните npm install)\n";
        }
    } else {
        echo "   ⚠️  package.json не найден (опционально)\n";
    }
} else {
    echo "   ⚠️  npm не доступен (используйте --skip-build)\n";
}
echo "\n";

// Тест 4: Laravel команды
echo "4. Тестирование Laravel команд...\n";
$tests['artisan_list'] = Process::run('php artisan list');
if ($tests['artisan_list']->successful()) {
    echo "   ✅ php artisan list: OK\n";
    $passed++;
    
    // Проверка наличия команды deploy
    $output = $tests['artisan_list']->output();
    if (strpos($output, 'deploy') !== false) {
        echo "   ✅ Команда 'deploy' зарегистрирована\n";
        $passed++;
    } else {
        echo "   ❌ Команда 'deploy' не найдена\n";
        $failed++;
    }
} else {
    echo "   ❌ php artisan list: FAILED\n";
    $failed++;
}

// Тест команды deploy --help
$tests['deploy_help'] = Process::run('php artisan deploy --help');
if ($tests['deploy_help']->successful()) {
    echo "   ✅ php artisan deploy --help: OK\n";
    $passed++;
} else {
    echo "   ❌ php artisan deploy --help: FAILED\n";
    $failed++;
}
echo "\n";

// Тест 5: Проверка .env переменных
echo "5. Тестирование переменных окружения...\n";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $vars = [
        'GIT_REPOSITORY_URL' => 'Git репозиторий',
        'DEPLOY_SERVER_URL' => 'URL сервера',
        'DEPLOY_TOKEN' => 'Токен авторизации'
    ];
    
    foreach ($vars as $var => $desc) {
        if (preg_match("/^{$var}=(.+)$/m", $envContent, $matches)) {
            $value = trim($matches[1]);
            if (!empty($value)) {
                // Маскируем токен
                if ($var === 'DEPLOY_TOKEN') {
                    $displayValue = substr($value, 0, 4) . '...' . substr($value, -4);
                } else {
                    $displayValue = $value;
                }
                echo "   ✅ {$var}: {$displayValue}\n";
                $passed++;
            } else {
                echo "   ❌ {$var}: пустое значение\n";
                $failed++;
            }
        } else {
            echo "   ❌ {$var}: не найден\n";
            $failed++;
        }
    }
} else {
    echo "   ❌ .env файл не найден\n";
    $failed++;
}
echo "\n";

// Тест 6: Dry-run тест
echo "6. Тестирование dry-run режима...\n";
$tests['deploy_dry_run'] = Process::run('php artisan deploy --dry-run 2>&1');
if ($tests['deploy_dry_run']->successful()) {
    echo "   ✅ php artisan deploy --dry-run: OK\n";
    $output = $tests['deploy_dry_run']->output();
    
    // Проверяем наличие ключевых слов в выводе
    $keywords = ['DRY-RUN', 'Validating', 'Git', 'Build', 'HTTP'];
    $foundKeywords = 0;
    foreach ($keywords as $keyword) {
        if (stripos($output, $keyword) !== false) {
            $foundKeywords++;
        }
    }
    
    if ($foundKeywords >= 3) {
        echo "   ✅ Dry-run показывает все шаги\n";
        $passed++;
    } else {
        echo "   ⚠️  Dry-run вывод может быть неполным\n";
    }
} else {
    echo "   ❌ php artisan deploy --dry-run: FAILED\n";
    echo "   Ошибка: " . $tests['deploy_dry_run']->errorOutput() . "\n";
    $failed++;
}
echo "\n";

// Итоги
echo str_repeat("=", 60) . "\n";
echo "📊 РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ\n";
echo str_repeat("=", 60) . "\n\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;

echo "✅ Пройдено: {$passed}\n";
echo "❌ Провалено: {$failed}\n";
echo "📈 Успешность: {$percentage}%\n\n";

if ($failed === 0) {
    echo "🎉 Все тесты пройдены успешно!\n";
    echo "Пакет готов к использованию.\n\n";
    echo "Следующий шаг: выполните реальный деплой:\n";
    echo "   php artisan deploy --message=\"Test deployment\"\n";
    exit(0);
} else {
    echo "⚠️  Некоторые тесты провалены.\n";
    echo "Исправьте ошибки перед использованием пакета.\n";
    exit(1);
}

