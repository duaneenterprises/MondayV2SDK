<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use MondayV2SDK\MondayClient;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;
use MondayV2SDK\Tests\TestHelpers\MockHelper;

/**
 * Simplified integration tests using MockHelper
 */
class SimplifiedIntegrationTest extends TestCase
{
    private MondayClient $client;
    private string $testApiToken = 'test-api-token';

    protected function setUp(): void
    {
        $this->client = new MondayClient(
            $this->testApiToken,
            [
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

    public function testSuccessfulQueryWithHelper(): void
    {
        // Create mock response using helper
        $mockResponse = MockHelper::createSuccessfulGraphQLResponse(
            'boards',
            [
            MockHelper::createSampleBoardData()
            ]
        );

        // Create mock HTTP client
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('query')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

        // Test the query
        $result = $this->client->query('query { boards { id name } }');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('boards', $result);
        $this->assertCount(1, $result['boards']);
    }

    public function testCreateItemWithHelper(): void
    {
        // Create mock response for item creation
        $mockResponse = MockHelper::createSuccessfulGraphQLResponse(
            'create_item',
            MockHelper::createSampleItemData()
        );

        // Create mock HTTP client
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('mutate')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

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
        $this->assertEquals('Test Item', $result['name']);
    }

    public function testGraphQLErrorWithHelper(): void
    {
        // Create mock error response
        $mockResponse = MockHelper::createGraphQLErrorResponse(
            [
            [
                'message' => 'Invalid query',
                'locations' => [['line' => 1, 'column' => 1]]
            ]
            ]
        );

        // Create mock HTTP client that throws exception
        $exception = new MondayApiException('GraphQL error', 400, $mockResponse);
        $mockHttpClient = MockHelper::createMockHttpClientWithException($exception);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

        // Test that exception is thrown
        $this->expectException(MondayApiException::class);
        $this->expectExceptionMessage('GraphQL error');

        $this->client->query('query { invalid_query }');
    }

    public function testRateLimitWithHelper(): void
    {
        // Create mock rate limit exception
        $exception = new RateLimitException('Rate limit exceeded', 60);
        $mockHttpClient = MockHelper::createMockHttpClientWithException($exception);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

        // Test that rate limit exception is thrown
        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->client->query('query { boards { id name } }');
    }

    public function testItemsServiceWithPagination(): void
    {
        // Create mock response with pagination
        $mockResponse = [
            'boards' => [
                [
                    'id' => '1234567890',
                    'items_page' => [
                        'cursor' => 'next-cursor-123',
                        'items' => [
                            MockHelper::createSampleItemData()
                        ]
                    ]
                ]
            ]
        ];

        // Create mock HTTP client
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('query')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

        // Test pagination
        $itemsService = $this->client->items();
        $result = $itemsService->getAll(1234567890, ['limit' => 100]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('cursor', $result);
        $this->assertEquals('next-cursor-123', $result['cursor']);
        $this->assertCount(1, $result['items']);
    }

    public function testSearchItemsByColumnValues(): void
    {
        // Create mock response for search
        $mockResponse = [
            'items_by_multiple_column_values' => [
                MockHelper::createSampleItemData()
            ]
        ];

        // Create mock HTTP client
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('query')
            ->once()
            ->andReturn($mockResponse);

        // Inject the mock
        MockHelper::injectMockHttpClient($this->client, $mockHttpClient);

        // Test search functionality
        $itemsService = $this->client->items();
        $result = $itemsService->searchByColumnValues(
            1234567890,
            [
            'status_01' => 'Working'
            ]
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test Item', $result[0]['name']);
    }
}
