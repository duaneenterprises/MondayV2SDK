<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use MondayV2SDK\Core\HttpClient;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\GuzzleClientInterface;
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * Tests for HttpClient with Guzzle mocking
 */
class HttpClientTest extends TestCase
{
    private string $testApiToken = 'test-api-token';
    private array $testConfig = [
        'timeout' => 30,
        'logging' => [
            'level' => 'debug',
            'enabled' => false
        ]
    ];

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSuccessfulQuery(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock successful response
        $mockResponse = new Response(
            200,
            [],
            json_encode(
                [
                'data' => [
                'boards' => [
                    ['id' => '1234567890', 'name' => 'Test Board']
                ]
                ]
                ]
            )
        );

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test the query
        $query = 'query { boards { id name } }';
        $result = $httpClient->query($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('boards', $result);
    }

    public function testSuccessfulMutation(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock successful response
        $mockResponse = new Response(
            200,
            [],
            json_encode(
                [
                'data' => [
                'create_item' => [
                    'id' => '1234567891',
                    'name' => 'New Item'
                ]
                ]
                ]
            )
        );

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test the mutation
        $mutation = 'mutation { create_item(board_id: 123, item_name: "Test") { id name } }';
        $result = $httpClient->mutate($mutation);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('create_item', $result);
    }

    public function testGraphQLErrorResponse(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock GraphQL error response
        $mockResponse = new Response(
            200,
            [],
            json_encode(
                [
                'errors' => [
                [
                    'message' => 'Invalid query',
                    'locations' => [['line' => 1, 'column' => 1]]
                ]
                ]
                ]
            )
        );

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test that GraphQL error is thrown
        $this->expectException(MondayApiException::class);

        $query = 'query { invalid_query }';
        $httpClient->query($query);
    }

    public function testRateLimitResponse(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock rate limit response
        $mockResponse = new Response(
            429,
            [
            'Retry-After' => '60',
            'X-RateLimit-Reset' => time() + 60
            ],
            json_encode(
                [
                'error_message' => 'Rate limit exceeded'
                ]
            )
        );

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test that rate limit exception is thrown
        $this->expectException(RateLimitException::class);

        $query = 'query { boards { id name } }';
        $httpClient->query($query);
    }

    public function testHttpErrorResponse(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock HTTP error response
        $mockResponse = new Response(
            500,
            [],
            json_encode(
                [
                'error_message' => 'Internal server error'
                ]
            )
        );

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test that HTTP error exception is thrown
        $this->expectException(MondayApiException::class);
        $this->expectExceptionMessage('HTTP error: 500');

        $query = 'query { boards { id name } }';
        $httpClient->query($query);
    }

    public function testNetworkException(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock network exception
        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andThrow(
                new RequestException(
                    'Network error',
                    new Request('POST', '/'),
                    new Response(500)
                )
            );

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test that network exception is thrown
        $this->expectException(MondayApiException::class);
        $this->expectExceptionMessage('Network error');

        $query = 'query { boards { id name } }';
        $httpClient->query($query);
    }

    public function testInvalidJsonResponse(): void
    {
        // Create a mock Guzzle client
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);

        // Mock invalid JSON response
        $mockResponse = new Response(200, [], 'invalid json');

        $mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with('', Mockery::type('array'))
            ->andReturn($mockResponse);

        // Create HttpClient and inject the mock
        $httpClient = new HttpClient($this->testApiToken, $this->testConfig);

        // Replace the Guzzle client using reflection
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);

        // Test that JSON decode exception is thrown
        $this->expectException(MondayApiException::class);

        $query = 'query { boards { id name } }';
        $httpClient->query($query);
    }
}
