<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use MondayV2SDK\MondayClient;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Services\BoardService;
use MondayV2SDK\Services\ItemService;
use MondayV2SDK\Services\ColumnService;
use MondayV2SDK\Services\UserService;
use MondayV2SDK\Services\WorkspaceService;

/**
 * Unit tests for MondayClient
 */
class MondayClientTest extends TestCase
{
    private MondayClient $client;
    private string $testApiToken = 'test-api-token';

    protected function setUp(): void
    {
        $this->client = new MondayClient(
            $this->testApiToken, [
            'timeout' => 30,
            'rate_limit' => [
                'minute_limit' => 100,
                'daily_limit' => 1000
            ],
            'logging' => [
                'level' => 'debug',
                'enabled' => false
            ]
            ]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Helper method to inject the mocked HttpClient into the MondayClient and all its services
     */
    private function injectMockedHttpClient($mockHttpClient): void
    {
        $reflection = new \ReflectionClass($this->client);
        
        // Inject into main client
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->client, $mockHttpClient);
        
        // Inject into all services
        $services = ['boardService', 'itemService', 'columnService', 'userService', 'workspaceService'];
        foreach ($services as $serviceName) {
            if ($reflection->hasProperty($serviceName)) {
                $serviceProperty = $reflection->getProperty($serviceName);
                $serviceProperty->setAccessible(true);
                $service = $serviceProperty->getValue($this->client);
                
                if ($service) {
                    $serviceReflection = new \ReflectionClass($service);
                    $serviceHttpClientProperty = $serviceReflection->getProperty('httpClient');
                    $serviceHttpClientProperty->setAccessible(true);
                    $serviceHttpClientProperty->setValue($service, $mockHttpClient);
                }
            }
        }
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(MondayClient::class, $this->client);
    }

    public function testGetHttpClient(): void
    {
        $httpClient = $this->client->getHttpClient();
        $this->assertInstanceOf(HttpClientInterface::class, $httpClient);
    }

    public function testGetRateLimiter(): void
    {
        $rateLimiter = $this->client->getRateLimiter();
        $this->assertInstanceOf(RateLimiter::class, $rateLimiter);
    }

    public function testGetLogger(): void
    {
        $logger = $this->client->getLogger();
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testBoardsService(): void
    {
        $boardService = $this->client->boards();
        $this->assertInstanceOf(BoardService::class, $boardService);
    }

    public function testItemsService(): void
    {
        $itemService = $this->client->items();
        $this->assertInstanceOf(ItemService::class, $itemService);
    }

    public function testColumnsService(): void
    {
        $columnService = $this->client->columns();
        $this->assertInstanceOf(ColumnService::class, $columnService);
    }

    public function testUsersService(): void
    {
        $userService = $this->client->users();
        $this->assertInstanceOf(UserService::class, $userService);
    }

    public function testWorkspacesService(): void
    {
        $workspaceService = $this->client->workspaces();
        $this->assertInstanceOf(WorkspaceService::class, $workspaceService);
    }

    public function testQueryMethod(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        $mockResponse = [
            'data' => [
                'boards' => [
                    ['id' => '1234567890', 'name' => 'Test Board']
                ]
            ]
        ];

        $mockHttpClient->shouldReceive('query')
            ->once()
            ->with('query { boards { id name } }', [])
            ->andReturn($mockResponse);

        // Replace the HTTP client in the MondayClient
        $this->injectMockedHttpClient($mockHttpClient);

        // Test the query method
        $result = $this->client->query('query { boards { id name } }');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('boards', $result['data']);
    }

    public function testMutateMethod(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        $mockResponse = [
            'create_item' => [
                'id' => '1234567891',
                'name' => 'New Item'
            ]
        ];

        $mockHttpClient->shouldReceive('mutate')
            ->once()
            ->with('mutation { create_item(board_id: 123, item_name: "Test") { id name } }', [])
            ->andReturn($mockResponse);

        // Replace the HTTP client in the MondayClient
        $this->injectMockedHttpClient($mockHttpClient);

        // Test the mutate method
        $result = $this->client->mutate('mutation { create_item(board_id: 123, item_name: "Test") { id name } }');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('create_item', $result);
    }

    public function testItemsServiceWithMockedHttpClient(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        $mockResponse = [
            'boards' => [
                [
                    'id' => '1234567890',
                    'items_page' => [
                        'cursor' => 'next-cursor',
                        'items' => [
                            [
                                'id' => '1234567891',
                                'name' => 'Test Item',
                                'state' => 'active'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $mockHttpClient->shouldReceive('query')
            ->once()
            ->andReturn($mockResponse);

        // Replace the HTTP client in the MondayClient
        $this->injectMockedHttpClient($mockHttpClient);

        // Test the items service
        $itemsService = $this->client->items();
        $result = $itemsService->getAll(1234567890);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('cursor', $result);
    }

    public function testCreateItemWithMockedHttpClient(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        $mockResponse = [
            'create_item' => [
                'id' => '1234567891',
                'name' => 'New Task',
                'state' => 'active',
                'created_at' => '2024-01-01T00:00:00Z'
            ]
        ];

        $mockHttpClient->shouldReceive('mutate')
            ->once()
            ->andReturn($mockResponse);

        // Replace the HTTP client in the MondayClient
        $this->injectMockedHttpClient($mockHttpClient);

        // Test creating an item
        $itemsService = $this->client->items();
        $result = $itemsService->create(
            [
            'board_id' => 1234567890,
            'item_name' => 'New Task'
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('New Task', $result['name']);
    }
} 