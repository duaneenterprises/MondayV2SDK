<?php

namespace MondayV2SDK\Core\Configuration;

/**
 * HTTP client configuration
 * 
 * Encapsulates all HTTP client settings with proper validation
 * and default values.
 */
class HttpClientConfig
{
    public const DEFAULT_TIMEOUT = 30;
    public const DEFAULT_CONNECT_TIMEOUT = 10;
    public const DEFAULT_API_BASE_URL = 'https://api.monday.com/v2';

    private string $apiToken;
    private int $timeout;
    private int $connectTimeout;
    private string $baseUrl;

    public function __construct(
        string $apiToken,
        int $timeout = self::DEFAULT_TIMEOUT,
        int $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT,
        string $baseUrl = self::DEFAULT_API_BASE_URL
    ) {
        $this->apiToken = $apiToken;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->baseUrl = $baseUrl;
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
            apiToken: $config['api_token'] ?? '',
            timeout: $config['timeout'] ?? self::DEFAULT_TIMEOUT,
            connectTimeout: $config['connect_timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT,
            baseUrl: $config['base_url'] ?? self::DEFAULT_API_BASE_URL
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
            'api_token' => $this->apiToken,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'base_url' => $this->baseUrl,
        ];
    }

    /**
     * Get API token
     * 
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * Get timeout
     * 
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get connect timeout
     * 
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * Get base URL
     * 
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Validate configuration values
     * 
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty($this->apiToken)) {
            throw new \InvalidArgumentException('API token is required');
        }

        if ($this->timeout <= 0) {
            throw new \InvalidArgumentException('Timeout must be positive');
        }

        if ($this->connectTimeout <= 0) {
            throw new \InvalidArgumentException('Connect timeout must be positive');
        }

        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Base URL cannot be empty');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Base URL must be a valid URL');
        }
    }
} 