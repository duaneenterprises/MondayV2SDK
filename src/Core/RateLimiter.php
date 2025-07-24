<?php

namespace MondayV2SDK\Core;

use MondayV2SDK\Exceptions\RateLimitException;

/**
 * Rate limiter for Monday.com API
 * 
 * Implements rate limiting logic to prevent hitting Monday.com's API limits.
 * Supports both per-minute and daily limits with automatic retry logic.
 * Includes periodic cleanup to prevent memory leaks.
 */
class RateLimiter
{
    private const DEFAULT_MINUTE_LIMIT = 100;
    private const DEFAULT_DAILY_LIMIT = 1000;
    private const DEFAULT_RETRY_DELAY = 60;
    private const DEFAULT_CLEANUP_INTERVAL = 300; // 5 minutes
    private const DEFAULT_MAX_ARRAY_SIZE = 10000; // Prevent arrays from growing too large
    
    private int $minuteLimit;
    private int $dailyLimit;
    private int $retryDelay;
    private int $cleanupInterval;
    private int $maxArraySize;
    
    /**
     * @var array<int, int> 
     */
    private array $requestTimes = [];
    /**
     * @var array<string, int> 
     */
    private array $dailyRequests = [];
    
    private Logger $logger;
    private int $lastCleanupTime = 0;
    private int $cleanupCount = 0;
    private int $totalCleanupTime = 0;

    /**
     * Constructor
     * 
     * @param array<string, mixed> $config Rate limiting configuration
     */
    public function __construct(array $config = [])
    {
        $this->minuteLimit = $config['minute_limit'] ?? self::DEFAULT_MINUTE_LIMIT;
        $this->dailyLimit = $config['daily_limit'] ?? self::DEFAULT_DAILY_LIMIT;
        $this->retryDelay = $config['retry_delay'] ?? self::DEFAULT_RETRY_DELAY;
        $this->cleanupInterval = $config['cleanup_interval'] ?? self::DEFAULT_CLEANUP_INTERVAL;
        $this->maxArraySize = $config['max_array_size'] ?? self::DEFAULT_MAX_ARRAY_SIZE;
        $this->logger = new Logger($config['logging'] ?? []);
    }

    /**
     * Check if request is allowed and wait if necessary
     * 
     * @throws RateLimitException
     */
    public function checkLimit(): void
    {
        $now = time();
        $today = date('Y-m-d', $now);
        
        // Perform periodic cleanup if needed
        $this->performPeriodicCleanup($now);
        
        // Clean up old request times (older than 1 minute)
        $this->cleanupOldRequests($now);
        
        // Check daily limit
        if (!$this->checkDailyLimit($today)) {
            $this->logger->warning(
                'Daily rate limit exceeded', [
                'daily_limit' => $this->dailyLimit,
                'daily_requests' => $this->getDailyRequestCount($today),
                ]
            );
            
            throw new RateLimitException(
                "Daily rate limit exceeded. Limit: {$this->dailyLimit}",
                $this->retryDelay
            );
        }
        
        // Check minute limit
        if (!$this->checkMinuteLimit($now)) {
            $waitTime = $this->calculateWaitTime($now);
            $this->logger->warning(
                'Minute rate limit exceeded, waiting', [
                'minute_limit' => $this->minuteLimit,
                'wait_time' => $waitTime,
                ]
            );
            
            // Only sleep if not in test environment
            if (!defined('PHPUNIT_RUNNING') || !PHPUNIT_RUNNING) {
                sleep($waitTime);
            } else {
                // In test environment, just throw the exception
                throw new RateLimitException(
                    "Minute rate limit exceeded. Limit: {$this->minuteLimit}",
                    $waitTime
                );
            }
        }
        
        // Record this request
        $this->recordRequest($now, $today);
    }

    /**
     * Perform periodic cleanup to prevent memory leaks
     * 
     * @param int $now Current timestamp
     */
    private function performPeriodicCleanup(int $now): void
    {
        // Check if it's time for periodic cleanup
        if ($now - $this->lastCleanupTime < $this->cleanupInterval) {
            return;
        }
        
        $cleanupStart = microtime(true);
        
        // Force cleanup of old requests
        $this->cleanupOldRequests($now);
        
        // Force cleanup of old daily records
        $this->cleanupOldDailyRecords();
        
        // Emergency cleanup if arrays are too large
        $this->emergencyCleanup();
        
        $cleanupEnd = microtime(true);
        $cleanupDuration = (int)(($cleanupEnd - $cleanupStart) * 1000); // Convert to milliseconds and cast to int
        
        $this->lastCleanupTime = $now;
        $this->cleanupCount++;
        $this->totalCleanupTime += $cleanupDuration;
        
        $this->logger->debug(
            'Periodic cleanup completed', [
            'cleanup_duration_ms' => $cleanupDuration,
            'request_times_count' => count($this->requestTimes),
            'daily_requests_count' => count($this->dailyRequests),
            'total_cleanups' => $this->cleanupCount,
            'avg_cleanup_time_ms' => $this->cleanupCount > 0 ? (int)($this->totalCleanupTime / $this->cleanupCount) : 0,
            ]
        );
    }

    /**
     * Emergency cleanup when arrays grow too large
     */
    private function emergencyCleanup(): void
    {
        $requestTimesSize = count($this->requestTimes);
        $dailyRequestsSize = count($this->dailyRequests);
        
        if ($requestTimesSize > $this->maxArraySize) {
            $this->logger->warning(
                'Emergency cleanup: request times array too large', [
                'current_size' => $requestTimesSize,
                'max_size' => $this->maxArraySize,
                ]
            );
            
            // Keep only the most recent requests (up to minute limit)
            $this->requestTimes = array_slice($this->requestTimes, -$this->minuteLimit, null, true);
        }
        
        if ($dailyRequestsSize > $this->maxArraySize) {
            $this->logger->warning(
                'Emergency cleanup: daily requests array too large', [
                'current_size' => $dailyRequestsSize,
                'max_size' => $this->maxArraySize,
                ]
            );
            
            // Keep only the most recent 30 days
            $this->dailyRequests = array_slice($this->dailyRequests, -30, null, true);
        }
    }

    /**
     * Clean up old request times
     * 
     * @param int $now Current timestamp
     */
    private function cleanupOldRequests(int $now): void
    {
        $cutoff = $now - 60; // Remove requests older than 1 minute
        $originalSize = count($this->requestTimes);
        
        $this->requestTimes = array_filter(
            $this->requestTimes,
            fn($time) => $time >= $cutoff
        );
        
        $newSize = count($this->requestTimes);
        $removed = $originalSize - $newSize;
        
        if ($removed > 0) {
            $this->logger->debug(
                'Cleaned up old request times', [
                'removed_count' => $removed,
                'remaining_count' => $newSize,
                ]
            );
        }
    }

    /**
     * Check daily limit
     * 
     * @param  string $today Today's date
     * @return bool
     */
    private function checkDailyLimit(string $today): bool
    {
        $dailyCount = $this->getDailyRequestCount($today);
        return $dailyCount < $this->dailyLimit;
    }

    /**
     * Check minute limit
     * 
     * @param  int $now Current timestamp
     * @return bool
     */
    private function checkMinuteLimit(int $now): bool
    {
        $minuteCount = count($this->requestTimes);
        return $minuteCount < $this->minuteLimit;
    }

    /**
     * Calculate wait time for rate limit
     * 
     * @param  int $now Current timestamp
     * @return int Wait time in seconds
     */
    private function calculateWaitTime(int $now): int
    {
        if (empty($this->requestTimes)) {
            return 1;
        }
        
        $oldestRequest = min($this->requestTimes);
        $timeSinceOldest = $now - $oldestRequest;
        
        // Wait until we have room for another request
        return max(1, (int)(60 - $timeSinceOldest));
    }

    /**
     * Record a request
     * 
     * @param int    $now   Current timestamp
     * @param string $today Today's date
     */
    private function recordRequest(int $now, string $today): void
    {
        $this->requestTimes[] = $now;
        
        if (!isset($this->dailyRequests[$today])) {
            $this->dailyRequests[$today] = 0;
        }
        $this->dailyRequests[$today]++;
    }

    /**
     * Get daily request count
     * 
     * @param  string $today Today's date
     * @return int
     */
    private function getDailyRequestCount(string $today): int
    {
        return $this->dailyRequests[$today] ?? 0;
    }

    /**
     * Clean up old daily records
     */
    private function cleanupOldDailyRecords(): void
    {
        $cutoff = date('Y-m-d', strtotime('-7 days'));
        $originalSize = count($this->dailyRequests);
        
        $this->dailyRequests = array_filter(
            $this->dailyRequests,
            fn($date) => $date >= $cutoff,
            ARRAY_FILTER_USE_KEY
        );
        
        $newSize = count($this->dailyRequests);
        $removed = $originalSize - $newSize;
        
        if ($removed > 0) {
            $this->logger->debug(
                'Cleaned up old daily records', [
                'removed_count' => $removed,
                'remaining_count' => $newSize,
                ]
            );
        }
    }

    /**
     * Get current usage statistics
     * 
     * @return array<string, mixed>
     */
    public function getUsageStats(): array
    {
        $now = time();
        $today = date('Y-m-d', $now);
        
        return [
            'minute_requests' => count($this->requestTimes),
            'minute_limit' => $this->minuteLimit,
            'daily_requests' => $this->getDailyRequestCount($today),
            'daily_limit' => $this->dailyLimit,
            'minute_remaining' => max(0, $this->minuteLimit - count($this->requestTimes)),
            'daily_remaining' => max(0, $this->dailyLimit - $this->getDailyRequestCount($today)),
            'cleanup_stats' => [
                'total_cleanups' => $this->cleanupCount,
                'avg_cleanup_time_ms' => $this->cleanupCount > 0 ? (int)($this->totalCleanupTime / $this->cleanupCount) : 0,
                'last_cleanup' => $this->lastCleanupTime > 0 ? date('Y-m-d H:i:s', $this->lastCleanupTime) : 'Never',
                'next_cleanup_in' => max(0, $this->cleanupInterval - ($now - $this->lastCleanupTime)),
            ],
            'memory_stats' => [
                'request_times_size' => count($this->requestTimes),
                'daily_requests_size' => count($this->dailyRequests),
                'max_array_size' => $this->maxArraySize,
            ],
        ];
    }

    /**
     * Force immediate cleanup (useful for testing or manual cleanup)
     */
    public function forceCleanup(): void
    {
        $now = time();
        
        // Bypass the time check for forced cleanup
        $cleanupStart = microtime(true);
        
        // Force cleanup of old requests
        $this->cleanupOldRequests($now);
        
        // Force cleanup of old daily records
        $this->cleanupOldDailyRecords();
        
        // Emergency cleanup if arrays are too large
        $this->emergencyCleanup();
        
        $cleanupEnd = microtime(true);
        $cleanupDuration = (int)(($cleanupEnd - $cleanupStart) * 1000); // Convert to milliseconds and cast to int
        
        $this->lastCleanupTime = $now;
        $this->cleanupCount++;
        $this->totalCleanupTime += $cleanupDuration;
        
        $this->logger->debug(
            'Forced cleanup completed', [
            'cleanup_duration_ms' => $cleanupDuration,
            'request_times_count' => count($this->requestTimes),
            'daily_requests_count' => count($this->dailyRequests),
            'total_cleanups' => $this->cleanupCount,
            'avg_cleanup_time_ms' => $this->cleanupCount > 0 ? (int)($this->totalCleanupTime / $this->cleanupCount) : 0,
            ]
        );
    }

    /**
     * Get cleanup configuration
     * 
     * @return array<string, mixed>
     */
    public function getCleanupConfig(): array
    {
        return [
            'cleanup_interval' => $this->cleanupInterval,
            'max_array_size' => $this->maxArraySize,
            'last_cleanup_time' => $this->lastCleanupTime,
            'total_cleanups' => $this->cleanupCount,
        ];
    }

    /**
     * Reset rate limiter (for testing)
     */
    public function reset(): void
    {
        $this->requestTimes = [];
        $this->dailyRequests = [];
        $this->lastCleanupTime = 0;
        $this->cleanupCount = 0;
        $this->totalCleanupTime = 0;
    }
} 