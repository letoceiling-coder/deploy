<?php

namespace LetoceilingCoder\Deploy\Services;

use Illuminate\Support\Facades\Log;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;

class LoggerService implements LoggerServiceInterface
{
    private string $logPath;
    private string $logChannel = 'deploy';

    public function __construct()
    {
        $this->logPath = storage_path('logs/deploy.log');
        $this->ensureLogDirectory();
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void
    {
        $directory = dirname($this->logPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Write log entry
     */
    private function writeLog(string $level, string $message, array $context = []): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($this->logPath, $logEntry, FILE_APPEND | LOCK_EX);

        // Also log to Laravel's log system
        Log::channel('single')->{$level}($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->writeLog('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->writeLog('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->writeLog('WARNING', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->writeLog('DEBUG', $message, $context);
    }

    public function step(string $step, string $message, array $context = []): void
    {
        $fullMessage = "[STEP: {$step}] {$message}";
        $this->info($fullMessage, $context);
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }
}

