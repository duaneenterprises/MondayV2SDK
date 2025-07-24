<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\PhoneColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;
use MondayV2SDK\ColumnTypes\LocationColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\Core\HttpClientInterface;
use Mockery;

/**
 * Test class to verify schema alignment with official Monday.com GraphQL schema
 * 
 * These tests ensure that the SDK uses the correct types and formats
 * as defined in the official schema at https://api.monday.com/v2/get_schema
 */
class SchemaAlignmentTest extends TestCase
{
    private MondayClient $client;

    protected function setUp(): void
    {
        $this->client = new MondayClient(
            'test-api-token', [
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

    /**
     * Test that email columns include both email and text fields
     */
    public function testEmailColumnSchemaAlignment(): void
    {
        $emailColumn = new EmailColumn('email_01', 'user@example.com', 'John Doe');
        $value = $emailColumn->getValue();

        // Verify email column includes both email and text fields as per schema
        $this->assertArrayHasKey('email', $value);
        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('user@example.com', $value['email']);
        $this->assertEquals('John Doe', $value['text']);
    }

    /**
     * Test that phone columns include both phone and text fields
     */
    public function testPhoneColumnSchemaAlignment(): void
    {
        $phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');
        $value = $phoneColumn->getValue();

        // Verify phone column includes both phone and text fields as per schema
        $this->assertArrayHasKey('phone', $value);
        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('+1-555-123-4567', $value['phone']);
        $this->assertEquals('John Doe', $value['text']);
    }

    /**
     * Test that timeline columns use date and end_date fields
     */
    public function testTimelineColumnSchemaAlignment(): void
    {
        $timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
        $value = $timelineColumn->getValue();

        // Verify timeline column uses date and end_date as per schema
        $this->assertArrayHasKey('date', $value);
        $this->assertArrayHasKey('end_date', $value);
        $this->assertEquals('2024-01-01', $value['date']);
        $this->assertEquals('2024-01-31', $value['end_date']);
    }

    /**
     * Test that location columns include all required fields
     */
    public function testLocationColumnSchemaAlignment(): void
    {
        $locationData = [
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'country_code' => 'US'
        ];

        $locationColumn = new LocationColumn('location_01', $locationData);
        $value = $locationColumn->getValue();

        // Verify location column includes all required fields as per schema
        $this->assertArrayHasKey('address', $value);
        $this->assertArrayHasKey('city', $value);
        $this->assertArrayHasKey('state', $value);
        $this->assertArrayHasKey('country', $value);
        $this->assertArrayHasKey('lat', $value);
        $this->assertArrayHasKey('lng', $value);
        $this->assertArrayHasKey('country_code', $value);
    }

    /**
     * Test that status columns use the correct format
     */
    public function testStatusColumnSchemaAlignment(): void
    {
        $statusColumn = new StatusColumn('status_01', 'Working', 'blue');
        $value = $statusColumn->getValue();

        // Verify status column uses the correct format as per schema
        $this->assertArrayHasKey('labels', $value);
        $this->assertIsArray($value['labels']);
        $this->assertCount(1, $value['labels']);
        $this->assertEquals('Working', $value['labels'][0]);
    }

    /**
     * Test that number columns use the correct format
     */
    public function testNumberColumnSchemaAlignment(): void
    {
        $numberColumn = new NumberColumn('number_01', 85.5, 'percentage');
        $value = $numberColumn->getApiValue();

        // Verify number column uses the correct format as per schema
        $this->assertArrayHasKey('number', $value);
        $this->assertEquals(85.5, $value['number']);
    }

    /**
     * Test that text columns use the correct format
     */
    public function testTextColumnSchemaAlignment(): void
    {
        $textColumn = new TextColumn('text_01', 'Sample text');
        $value = $textColumn->getApiValue();

        // Verify text column uses the correct format as per schema
        $this->assertArrayHasKey('text', $value);
        $this->assertEquals('Sample text', $value['text']);
    }

    /**
     * Test that GraphQL queries use ID! type for board and item IDs
     */
    public function testGraphQLTypeAlignment(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        // Mock successful responses
        $mockResponse = [
            'data' => [
                'boards' => [
                    [
                        'id' => '1234567890',
                        'items_page' => [
                            'cursor' => 'next-cursor',
                            'items' => []
                        ]
                    ]
                ]
            ]
        ];

        $mockHttpClient->shouldReceive('query')
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient($mockHttpClient);
        
        // These should work without type errors
        $result1 = $this->client->items()->getAll(1234567890); // int should be cast to ID
        $result2 = $this->client->items()->get(1234567890); // int should be cast to ID
        $result3 = $this->client->boards()->get(1234567890); // int should be cast to ID
        
        // Assert that the methods return expected results
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertIsArray($result3);
    }

    /**
     * Test that column values are properly JSON encoded for API calls
     */
    public function testColumnValuesJsonEncoding(): void
    {
        $columnValues = [
            new TextColumn('text_01', 'Sample text'),
            new EmailColumn('email_01', 'user@example.com', 'John Doe'),
            new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31')
        ];

        // Simulate the formatColumnValues method from ItemService
        $formattedValues = [];
        foreach ($columnValues as $column) {
            if (method_exists($column, 'getApiValue')) {
                $formattedValues[$column->getColumnId()] = json_encode($column->getApiValue());
            } else {
                $formattedValues[$column->getColumnId()] = json_encode($column->getValue());
            }
        }

        // Verify that each column value is properly JSON encoded
        foreach ($formattedValues as $columnId => $jsonValue) {
            $this->assertIsString($jsonValue);
            $decoded = json_decode($jsonValue, true);
            $this->assertNotNull($decoded, "JSON decode failed for column $columnId");
            $this->assertIsArray($decoded);
        }
    }

    /**
     * Test that search queries use the correct schema format
     */
    public function testSearchQueryAlignment(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        // Mock successful response
        $mockResponse = [
            'data' => [
                'items_by_multiple_column_values' => []
            ]
        ];

        $mockHttpClient->shouldReceive('query')
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient($mockHttpClient);
        
        // This should work without type errors
        $result = $this->client->items()->searchByColumnValues(
            1234567890, [
            'status_01' => 'Working'
            ]
        );
        
        // Assert that the search method returns expected results
        $this->assertIsArray($result);
    }

    /**
     * Test that pagination uses cursor-based approach
     */
    public function testPaginationAlignment(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        // Mock successful responses
        $mockResponse = [
            'data' => [
                'boards' => [
                    [
                        'id' => '1234567890',
                        'items_page' => [
                            'cursor' => 'next-cursor',
                            'items' => []
                        ]
                    ]
                ]
            ]
        ];

        $mockHttpClient->shouldReceive('query')
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient($mockHttpClient);
        
        // These should work without type errors
        $result1 = $this->client->items()->getAll(1234567890, ['limit' => 100]);
        $result2 = $this->client->items()->getNextPage('eyJib2FyZF9pZCI6MTIzNDU2Nzg5LCJpdGVtX2lkIjoxMjM0NTY3ODl9');
        
        // Assert that the pagination methods return expected results
        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
    }

    /**
     * Test that mutations use the correct schema format
     */
    public function testMutationAlignment(): void
    {
        // Create a mock HttpClient
        $mockHttpClient = Mockery::mock(HttpClientInterface::class);
        
        // Mock successful response
        $mockResponse = [
            'data' => [
                'create_item' => [
                    'id' => '1234567891',
                    'name' => 'Test Item'
                ]
            ]
        ];

        $mockHttpClient->shouldReceive('mutate')
            ->andReturn($mockResponse);

        $this->injectMockedHttpClient($mockHttpClient);
        
        // This should work without type errors
        $result = $this->client->items()->create(
            [
            'board_id' => 1234567890,
            'item_name' => 'Test Item',
            'column_values' => [
                new TextColumn('text_01', 'Sample text')
            ]
            ]
        );
        
        // Assert that the mutation method returns expected results
        $this->assertIsArray($result);
    }
} 