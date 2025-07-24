<?php

namespace MondayV2SDK\Tests\TestHelpers;

use Mockery;
use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\GuzzleClientInterface;
use MondayV2SDK\MondayClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * Helper class for creating mocks in tests
 */
class MockHelper
{
    /**
     * Create a mock HttpClient with a successful response
     */
    public static function createMockHttpClient(array $responseData = []): \Mockery\MockInterface
    {
        $defaultResponse = [
            'boards' => [
                ['id' => '1234567890', 'name' => 'Test Board']
            ]
        ];

        $mockResponse = array_merge($defaultResponse, $responseData);

        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('query')
            ->andReturn($mockResponse);
        $mockHttpClient->shouldReceive('mutate')
            ->andReturn($mockResponse);

        return $mockHttpClient;
    }

    /**
     * Create a mock HttpClient that throws an exception
     */
    public static function createMockHttpClientWithException(\Exception $exception): \Mockery\MockInterface
    {
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $mockHttpClient->shouldReceive('query')
            ->andThrow($exception);
        $mockHttpClient->shouldReceive('mutate')
            ->andThrow($exception);

        return $mockHttpClient;
    }

    /**
     * Create a mock Guzzle client with a successful response
     */
    public static function createMockGuzzleClient(array $responseData = []): \Mockery\MockInterface
    {
        $defaultResponse = [
            'boards' => [
                ['id' => '1234567890', 'name' => 'Test Board']
            ]
        ];

        $mockResponse = array_merge($defaultResponse, $responseData);

        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);
        $mockGuzzleClient->shouldReceive('post')
            ->with('/', Mockery::type('array'))
            ->andReturn(new Response(200, [], json_encode($mockResponse)));

        return $mockGuzzleClient;
    }

    /**
     * Create a mock Guzzle client that returns an error response
     */
    public static function createMockGuzzleClientWithError(int $statusCode, array $headers = [], string $body = ''): \Mockery\MockInterface
    {
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);
        $mockGuzzleClient->shouldReceive('post')
            ->with('/', Mockery::type('array'))
            ->andReturn(new Response($statusCode, $headers, $body));

        return $mockGuzzleClient;
    }

    /**
     * Create a mock Guzzle client that throws an exception
     */
    public static function createMockGuzzleClientWithException(\Exception $exception): \Mockery\MockInterface
    {
        $mockGuzzleClient = Mockery::mock(GuzzleClientInterface::class);
        $mockGuzzleClient->shouldReceive('post')
            ->with('/', Mockery::type('array'))
            ->andThrow($exception);

        return $mockGuzzleClient;
    }

    /**
     * Inject a mock HttpClient into a MondayClient
     */
    public static function injectMockHttpClient(MondayClient $client, $mockHttpClient): void
    {
        $reflection = new \ReflectionClass($client);

        // Inject into main client
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($client, $mockHttpClient);

        // Inject into all services
        $services = ['boardService', 'itemService', 'columnService', 'userService', 'workspaceService'];
        foreach ($services as $serviceName) {
            if ($reflection->hasProperty($serviceName)) {
                $serviceProperty = $reflection->getProperty($serviceName);
                $serviceProperty->setAccessible(true);
                $service = $serviceProperty->getValue($client);

                if ($service) {
                    $serviceReflection = new \ReflectionClass($service);
                    $serviceHttpClientProperty = $serviceReflection->getProperty('httpClient');
                    $serviceHttpClientProperty->setAccessible(true);
                    $serviceHttpClientProperty->setValue($service, $mockHttpClient);
                }
            }
        }
    }

    /**
     * Inject a mock Guzzle client into an HttpClient
     */
    public static function injectMockGuzzleClient(HttpClientInterface $httpClient, GuzzleClientInterface $mockGuzzleClient): void
    {
        $reflection = new \ReflectionClass($httpClient);
        $guzzleClientProperty = $reflection->getProperty('guzzleClient');
        $guzzleClientProperty->setAccessible(true);
        $guzzleClientProperty->setValue($httpClient, $mockGuzzleClient);
    }

    /**
     * Create sample board data
     */
    public static function createSampleBoardData(): array
    {
        return [
            'id' => '1234567890',
            'name' => 'Test Board',
            'description' => 'A test board',
            'state' => 'active',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z'
        ];
    }

    /**
     * Create sample item data
     */
    public static function createSampleItemData(): array
    {
        return [
            'id' => '1234567891',
            'name' => 'Test Item',
            'state' => 'active',
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
            'column_values' => [
                [
                    'id' => 'text_01',
                    'value' => 'Sample text',
                    'text' => 'Sample text',
                    'type' => 'text'
                ]
            ]
        ];
    }

    /**
     * Create sample column data
     */
    public static function createSampleColumnData(): array
    {
        return [
            'id' => 'text_01',
            'title' => 'Text Column',
            'type' => 'text',
            'settings_str' => '{}'
        ];
    }

    /**
     * Create a successful GraphQL response
     */
    public static function createSuccessfulGraphQLResponse(string $operation, array $data): array
    {
        return [
            $operation => $data
        ];
    }

    /**
     * Create a GraphQL error response
     */
    public static function createGraphQLErrorResponse(array $errors): array
    {
        return [
            'errors' => $errors
        ];
    }

    /**
     * Create a rate limit response
     */
    public static function createRateLimitResponse(int $retryAfter = 60): Response
    {
        return new Response(
            429,
            [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Reset' => (string) (time() + $retryAfter)
            ],
            json_encode(
                [
                'error_message' => 'Rate limit exceeded'
                ]
            )
        );
    }
}
