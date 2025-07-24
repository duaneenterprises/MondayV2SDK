<?php

namespace MondayV2SDK\Core;

use MondayV2SDK\Core\Configuration\Configuration;
use MondayV2SDK\Core\Configuration\HttpClientConfig;
use MondayV2SDK\Core\Configuration\RateLimitConfig;
use MondayV2SDK\Core\Configuration\LoggingConfig;
use MondayV2SDK\Services\ItemService;
use MondayV2SDK\Services\BoardService;
use MondayV2SDK\Services\ColumnService;

/**
 * Service factory for creating and managing SDK services
 *
 * Provides centralized service creation with proper dependency injection
 * and configuration management.
 */
class ServiceFactory
{
    private Configuration $configuration;
    private HttpClient $httpClient;
    private RateLimiter $rateLimiter;
    private Logger $logger;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->initializeServices();
    }

    /**
     * Create service factory from array configuration
     *
     * @param  array<string, mixed> $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        $configuration = Configuration::fromLegacyArray($config);
        return new self($configuration);
    }

    /**
     * Create service factory from configuration object
     *
     * @param  Configuration $configuration
     * @return self
     */
    public static function fromConfiguration(Configuration $configuration): self
    {
        return new self($configuration);
    }

    /**
     * Initialize core services
     */
    private function initializeServices(): void
    {
        $httpConfig = $this->configuration->getHttpClient();
        $rateLimitConfig = $this->configuration->getRateLimit();
        $loggingConfig = $this->configuration->getLogging();

        // Initialize logger
        $this->logger = new Logger($loggingConfig->toArray());

        // Initialize rate limiter
        $this->rateLimiter = new RateLimiter($rateLimitConfig->toArray());

        // Initialize HTTP client
        $this->httpClient = new HttpClient($httpConfig->getApiToken());
    }

    /**
     * Get HTTP client
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get rate limiter
     *
     * @return RateLimiter
     */
    public function getRateLimiter(): RateLimiter
    {
        return $this->rateLimiter;
    }

    /**
     * Get logger
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Get configuration
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * Create item service
     *
     * @return ItemService
     */
    public function createItemService(): ItemService
    {
        return new ItemService(
            $this->httpClient,
            $this->rateLimiter,
            $this->logger
        );
    }

    /**
     * Create board service
     *
     * @return BoardService
     */
    public function createBoardService(): BoardService
    {
        return new BoardService(
            $this->httpClient,
            $this->rateLimiter,
            $this->logger
        );
    }

    /**
     * Create column service
     *
     * @return ColumnService
     */
    public function createColumnService(): ColumnService
    {
        return new ColumnService(
            $this->httpClient,
            $this->rateLimiter,
            $this->logger
        );
    }

    /**
     * Create all services
     *
     * @return array<string, object>
     */
    public function createAllServices(): array
    {
        return [
            'items' => $this->createItemService(),
            'boards' => $this->createBoardService(),
            'columns' => $this->createColumnService(),
        ];
    }

    /**
     * Reset services (useful for testing)
     */
    public function reset(): void
    {
        $this->initializeServices();
    }

    /**
     * Get service by name
     *
     * @param  string $serviceName
     * @return object|null
     */
    public function getService(string $serviceName): ?object
    {
        return match ($serviceName) {
            'items' => $this->createItemService(),
            'boards' => $this->createBoardService(),
            'columns' => $this->createColumnService(),
            default => null,
        };
    }

    /**
     * Check if service exists
     *
     * @param  string $serviceName
     * @return bool
     */
    public function hasService(string $serviceName): bool
    {
        return in_array($serviceName, ['items', 'boards', 'columns']);
    }

    /**
     * Get available service names
     *
     * @return array<string>
     */
    public function getAvailableServices(): array
    {
        return ['items', 'boards', 'columns'];
    }
}
