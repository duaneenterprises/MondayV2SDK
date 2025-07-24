<?php

namespace MondayV2SDK\Core;

/**
 * Logger for Monday.com SDK
 * 
 * Provides logging functionality for debugging and monitoring API interactions.
 * Supports different log levels and can be configured to write to files or other outputs.
 */
class Logger
{
    private const LOG_LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];
    
    private string $logLevel;
    private ?string $logFile;
    private bool $enabled;

    /**
     * Constructor
     * 
     * @param array<string, mixed> $config Logging configuration
     */
    public function __construct(array $config = [])
    {
        $this->logLevel = $config['level'] ?? 'info';
        $this->logFile = $config['file'] ?? null;
        $this->enabled = $config['enabled'] ?? true;
    }

    /**
     * Log a debug message
     * 
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a critical message
     * 
     * @param string               $message
     * @param array<string, mixed> $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log a message with the specified level
     * 
     * @param string               $level
     * @param string               $message
     * @param array<string, mixed> $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if (!$this->enabled || !$this->shouldLog($level)) {
            return;
        }

        $logEntry = $this->formatLogEntry($level, $message, $context);
        
        if ($this->logFile) {
            $this->writeToFile($logEntry);
        } else {
            $this->writeToErrorLog($logEntry);
        }
    }

    /**
     * Check if the message should be logged based on current log level
     * 
     * @param  string $level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        return self::LOG_LEVELS[$level] >= self::LOG_LEVELS[$this->logLevel];
    }

    /**
     * Format a log entry
     * 
     * @param  string               $level
     * @param  string               $message
     * @param  array<string, mixed> $context
     * @return string
     */
    private function formatLogEntry(string $level, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        
        return "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    }

    /**
     * Write log entry to file
     * 
     * @param string $logEntry
     */
    private function writeToFile(string $logEntry): void
    {
        if (!$this->logFile) {
            return;
        }

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Write log entry to error log
     * 
     * @param string $logEntry
     */
    private function writeToErrorLog(string $logEntry): void
    {
        error_log(trim($logEntry));
    }

    /**
     * Set log level
     * 
     * @param string $level
     */
    public function setLogLevel(string $level): void
    {
        if (isset(self::LOG_LEVELS[$level])) {
            $this->logLevel = $level;
        }
    }

    /**
     * Set log file
     * 
     * @param string $logFile
     */
    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    /**
     * Enable or disable logging
     * 
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Get current log level
     * 
     * @return string
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * Get log file path
     * 
     * @return string|null
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * Check if logging is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
} 