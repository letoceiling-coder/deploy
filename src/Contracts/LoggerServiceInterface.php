<?php

namespace LetoceilingCoder\Deploy\Contracts;

interface LoggerServiceInterface
{
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void;

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log step with prefix
     */
    public function step(string $step, string $message, array $context = []): void;

    /**
     * Get log file path
     */
    public function getLogPath(): string;
}

