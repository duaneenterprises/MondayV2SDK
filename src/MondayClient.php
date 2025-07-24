<?php

namespace MondayV2SDK;

use MondayV2SDK\Services\BoardService;
use MondayV2SDK\Services\ItemService;
use MondayV2SDK\Services\ColumnService;
use MondayV2SDK\Services\UserService;
use MondayV2SDK\Services\WorkspaceService;
use MondayV2SDK\Core\HttpClient;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;

/**
 * Main client for Monday.com API V2
 *
 * This class serves as the entry point for all Monday.com API operations.
 * It provides access to various services for managing boards, items, columns, etc.
 */
class MondayClient
{
    private HttpClientInterface $httpClient;
    private RateLimiter $rateLimiter;
    private Logger $logger;

    private BoardService $boardService;
    private ItemService $itemService;
    private ColumnService $columnService;
    private UserService $userService;
    private WorkspaceService $workspaceService;

    /**
     * Constructor
     *
     * @param string               $apiToken Monday.com API token
     * @param array<string, mixed> $config   Configuration options
     */
    public function __construct(string $apiToken, array $config = [])
    {
        $this->httpClient = new HttpClient($apiToken, $config);
        $this->rateLimiter = new RateLimiter($config['rate_limit'] ?? []);
        $this->logger = new Logger($config['logging'] ?? []);

        $this->initializeServices();
    }

    /**
     * Initialize all service instances
     */
    private function initializeServices(): void
    {
        $this->boardService = new BoardService($this->httpClient, $this->rateLimiter, $this->logger);
        $this->itemService = new ItemService($this->httpClient, $this->rateLimiter, $this->logger);
        $this->columnService = new ColumnService($this->httpClient, $this->rateLimiter, $this->logger);
        $this->userService = new UserService($this->httpClient, $this->rateLimiter, $this->logger);
        $this->workspaceService = new WorkspaceService($this->httpClient, $this->rateLimiter, $this->logger);
    }

    /**
     * Get board service
     */
    public function boards(): BoardService
    {
        return $this->boardService;
    }

    /**
     * Get item service
     */
    public function items(): ItemService
    {
        return $this->itemService;
    }

    /**
     * Get column service
     */
    public function columns(): ColumnService
    {
        return $this->columnService;
    }

    /**
     * Get user service
     */
    public function users(): UserService
    {
        return $this->userService;
    }

    /**
     * Get workspace service
     */
    public function workspaces(): WorkspaceService
    {
        return $this->workspaceService;
    }

    /**
     * Get HTTP client (for advanced usage)
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get rate limiter
     */
    public function getRateLimiter(): RateLimiter
    {
        return $this->rateLimiter;
    }

    /**
     * Get logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Execute a custom GraphQL query
     *
     * @param  string               $query     GraphQL query
     * @param  array<string, mixed> $variables Query variables
     * @return array<string, mixed> Response data
     */
    public function query(string $query, array $variables = []): array
    {
        return $this->httpClient->query($query, $variables);
    }

    /**
     * Execute a custom GraphQL mutation
     *
     * @param  string               $mutation  GraphQL mutation
     * @param  array<string, mixed> $variables Mutation variables
     * @return array<string, mixed> Response data
     */
    public function mutate(string $mutation, array $variables = []): array
    {
        return $this->httpClient->mutate($mutation, $variables);
    }
}
