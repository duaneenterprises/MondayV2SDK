<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use MondayV2SDK\MondayClient;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Exceptions\MondayApiException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * Integration tests for MondayClient with proper mocking
 */
class MondayClientIntegrationTest extends TestCase
{
    private MondayClient $client;
    /**
     * @var \Mockery\MockInterface|HttpClient 
     */
    private $httpClient;
    private string $testApiToken = 'test-api-token';

    protected function setUp(): void
    {
        // Create a mock HttpClient
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        
        // Create the client with mocked dependencies
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
    private function injectMockedHttpClient(): void
    {
        $reflection = new \ReflectionClass($this->client);
        
        // Inject into main client
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->client, $this->httpClient);
        
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
                    $serviceHttpClientProperty->setValue($service, $this->httpClient);
                }
            }
        }
    }

    public function testQueryMethodWithMockedResponse(): void
    {
        // Mock successful response
        $mockResponse = [
            'data' => [
                'boards' => [
                    [
                        'id' => '1234567890',
                        'name' => 'Test Board',
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

        $this->httpClient->shouldReceive('query')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('array')
            )
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient();

        // Test the query method
        $query = 'query { boards { id name } }';
        $result = $this->client->query($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('boards', $result['data']);
    }

    public function testMutateMethodWithMockedResponse(): void
    {
        // Mock successful mutation response
        $mockResponse = [
            'data' => [
                'create_item' => [
                    'id' => '1234567891',
                    'name' => 'New Item',
                    'state' => 'active'
                ]
            ]
        ];

        $this->httpClient->shouldReceive('mutate')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('array')
            )
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient();

        // Test the mutate method
        $mutation = 'mutation { create_item(board_id: 123, item_name: "Test") { id name } }';
        $result = $this->client->mutate($mutation);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('create_item', $result['data']);
    }

    public function testQueryMethodWithErrorResponse(): void
    {
        // Mock error response
        $this->httpClient->shouldReceive('query')
            ->once()
            ->andThrow(
                new MondayApiException(
                    'GraphQL error', 400, [
                    'errors' => [
                    [
                        'message' => 'Invalid query',
                        'locations' => [['line' => 1, 'column' => 1]]
                    ]
                    ]
                    ]
                )
            );

        $this->injectMockedHttpClient();

        // Test that the exception is thrown
        $this->expectException(MondayApiException::class);
        $this->expectExceptionMessage('GraphQL error');

        $query = 'query { invalid_query }';
        $this->client->query($query);
    }

    public function testItemsServiceWithMockedHttpClient(): void
    {
        // Mock successful response for items
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

        $this->httpClient->shouldReceive('query')
            ->once()
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient();

        // Test the items service
        $itemsService = $this->client->items();
        $result = $itemsService->getAll(1234567890);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('cursor', $result);
    }

    public function testCreateItemWithMockedHttpClient(): void
    {
        // Mock successful response for creating an item
        $mockResponse = [
            'create_item' => [
                'id' => '1234567891',
                'name' => 'New Task',
                'state' => 'active',
                'created_at' => '2024-01-01T00:00:00Z'
            ]
        ];

        $this->httpClient->shouldReceive('mutate')
            ->once()
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient();

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

    public function testRateLimitHandling(): void
    {
        // Mock rate limit response
        $this->httpClient->shouldReceive('query')
            ->once()
            ->andThrow(new \MondayV2SDK\Exceptions\RateLimitException('Rate limit exceeded', 60));

        $this->injectMockedHttpClient();

        // Test that rate limit exception is thrown
        $this->expectException(\MondayV2SDK\Exceptions\RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $query = 'query { boards { id name } }';
        $this->client->query($query);
    }
} 