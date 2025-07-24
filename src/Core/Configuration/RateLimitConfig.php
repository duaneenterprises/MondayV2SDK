<?php

namespace MondayV2SDK\Core\Configuration;

/**
 * Rate limiting configuration
 *
 * Encapsulates all rate limiting settings with proper validation
 * and default values.
 */
class RateLimitConfig
{
    public const DEFAULT_MINUTE_LIMIT = 100;
    public const DEFAULT_DAILY_LIMIT = 1000;
    public const DEFAULT_RETRY_DELAY = 60;
    public const DEFAULT_CLEANUP_INTERVAL = 300;
    public const DEFAULT_MAX_ARRAY_SIZE = 10000;

    private int $minuteLimit;
    private int $dailyLimit;
    private int $retryDelay;
    private int $cleanupInterval;
    private int $maxArraySize;

    public function __construct(
        int $minuteLimit = self::DEFAULT_MINUTE_LIMIT,
        int $dailyLimit = self::DEFAULT_DAILY_LIMIT,
        int $retryDelay = self::DEFAULT_RETRY_DELAY,
        int $cleanupInterval = self::DEFAULT_CLEANUP_INTERVAL,
        int $maxArraySize = self::DEFAULT_MAX_ARRAY_SIZE
    ) {
        $this->minuteLimit = $minuteLimit;
        $this->dailyLimit = $dailyLimit;
        $this->retryDelay = $retryDelay;
        $this->cleanupInterval = $cleanupInterval;
        $this->maxArraySize = $maxArraySize;
        $this->validate();
    }

    /**
     * Create from array configuration
     *
     * @param  array<string, mixed> $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            minuteLimit: $config['minute_limit'] ?? self::DEFAULT_MINUTE_LIMIT,
            dailyLimit: $config['daily_limit'] ?? self::DEFAULT_DAILY_LIMIT,
            retryDelay: $config['retry_delay'] ?? self::DEFAULT_RETRY_DELAY,
            cleanupInterval: $config['cleanup_interval'] ?? self::DEFAULT_CLEANUP_INTERVAL,
            maxArraySize: $config['max_array_size'] ?? self::DEFAULT_MAX_ARRAY_SIZE
        );
    }

    /**
     * Convert to array for backward compatibility
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'minute_limit' => $this->minuteLimit,
            'daily_limit' => $this->dailyLimit,
            'retry_delay' => $this->retryDelay,
            'cleanup_interval' => $this->cleanupInterval,
            'max_array_size' => $this->maxArraySize,
        ];
    }

    /**
     * Get minute limit
     *
     * @return int
     */
    public function getMinuteLimit(): int
    {
        return $this->minuteLimit;
    }

    /**
     * Get daily limit
     *
     * @return int
     */
    public function getDailyLimit(): int
    {
        return $this->dailyLimit;
    }

    /**
     * Get retry delay
     *
     * @return int
     */
    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * Get cleanup interval
     *
     * @return int
     */
    public function getCleanupInterval(): int
    {
        return $this->cleanupInterval;
    }

    /**
     * Get max array size
     *
     * @return int
     */
    public function getMaxArraySize(): int
    {
        return $this->maxArraySize;
    }

    /**
     * Validate configuration values
     *
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        if ($this->minuteLimit <= 0) {
            throw new \InvalidArgumentException('Minute limit must be positive');
        }

        if ($this->dailyLimit <= 0) {
            throw new \InvalidArgumentException('Daily limit must be positive');
        }

        if ($this->retryDelay < 0) {
            throw new \InvalidArgumentException('Retry delay cannot be negative');
        }

        if ($this->cleanupInterval <= 0) {
            throw new \InvalidArgumentException('Cleanup interval must be positive');
        }

        if ($this->maxArraySize <= 0) {
            throw new \InvalidArgumentException('Max array size must be positive');
        }

        if ($this->dailyLimit < $this->minuteLimit) {
            throw new \InvalidArgumentException('Daily limit cannot be less than minute limit');
        }
    }
}
