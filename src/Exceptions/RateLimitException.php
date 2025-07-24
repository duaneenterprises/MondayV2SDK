<?php

namespace MondayV2SDK\Exceptions;

/**
 * Exception thrown when rate limit is exceeded
 *
 * This exception is thrown when the Monday.com API rate limit is exceeded
 * or when the SDK's internal rate limiter prevents a request.
 */
class RateLimitException extends \Exception
{
    private int $retryAfter;

    /**
     * Constructor
     *
     * @param string      $message    Error message
     * @param int         $retryAfter Seconds to wait before retrying
     * @param ?\Throwable $previous   Previous exception
     */
    public function __construct(string $message = '', int $retryAfter = 60, ?\Throwable $previous = null)
    {
        parent::__construct($message, 429, $previous);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the number of seconds to wait before retrying
     *
     * @return int
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the retry after time as a DateTime
     *
     * @return \DateTime
     */
    public function getRetryAfterDateTime(): \DateTime
    {
        return new \DateTime("+{$this->retryAfter} seconds");
    }

    /**
     * Check if enough time has passed to retry
     *
     * @param  \DateTime $lastAttempt When the last attempt was made
     * @return bool
     */
    public function canRetry(\DateTime $lastAttempt): bool
    {
        $now = new \DateTime();
        $retryTime = clone $lastAttempt;
        $retryTime->add(new \DateInterval("PT{$this->retryAfter}S"));

        return $now >= $retryTime;
    }
}
