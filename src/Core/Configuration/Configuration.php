<?php

namespace MondayV2SDK\Core\Configuration;

/**
 * Main configuration class
 *
 * Combines all configuration components into a single,
 * validated configuration object.
 */
class Configuration
{
    private HttpClientConfig $httpClient;
    private RateLimitConfig $rateLimit;
    private LoggingConfig $logging;

    public function __construct(
        HttpClientConfig $httpClient,
        RateLimitConfig $rateLimit,
        LoggingConfig $logging
    ) {
        $this->httpClient = $httpClient;
        $this->rateLimit = $rateLimit;
        $this->logging = $logging;
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
            httpClient: HttpClientConfig::fromArray($config['http_client'] ?? []),
            rateLimit: RateLimitConfig::fromArray($config['rate_limit'] ?? []),
            logging: LoggingConfig::fromArray($config['logging'] ?? [])
        );
    }

    /**
     * Create from legacy configuration format
     *
     * @param  array<string, mixed> $config
     * @return self
     */
    public static function fromLegacyArray(array $config): self
    {
        // Extract API token from root level for backward compatibility
        $apiToken = $config['api_token'] ?? '';

        return new self(
            httpClient: new HttpClientConfig($apiToken),
            rateLimit: RateLimitConfig::fromArray($config),
            logging: LoggingConfig::fromArray($config)
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
            'http_client' => $this->httpClient->toArray(),
            'rate_limit' => $this->rateLimit->toArray(),
            'logging' => $this->logging->toArray(),
        ];
    }

    /**
     * Get HTTP client configuration
     *
     * @return HttpClientConfig
     */
    public function getHttpClient(): HttpClientConfig
    {
        return $this->httpClient;
    }

    /**
     * Get rate limit configuration
     *
     * @return RateLimitConfig
     */
    public function getRateLimit(): RateLimitConfig
    {
        return $this->rateLimit;
    }

    /**
     * Get logging configuration
     *
     * @return LoggingConfig
     */
    public function getLogging(): LoggingConfig
    {
        return $this->logging;
    }

    /**
     * Get API token (convenience method)
     *
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->httpClient->getApiToken();
    }
}
