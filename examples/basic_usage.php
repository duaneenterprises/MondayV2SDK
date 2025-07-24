<?php

/**
 * Basic Usage Example for Monday.com V2 SDK
 * 
 * This example demonstrates the basic usage of the Monday.com V2 SDK
 * including creating items, managing boards, and handling different column types.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\PhoneColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\LocationColumn;
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;

// Initialize the client
$client = new MondayClient('your-api-token-here', [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ],
    'logging' => [
        'level' => 'info',
        'enabled' => true,
        'file' => __DIR__ . '/monday-sdk.log'
    ]
]);

try {
    echo "=== Monday.com V2 SDK Basic Usage Example ===\n\n";

    // Example 1: Get all boards
    echo "1. Getting all boards...\n";
    $boards = $client->boards()->getAll();
    echo "Found " . count($boards) . " boards\n\n";

    // Example 2: Get a specific board
    if (!empty($boards)) {
        $boardId = $boards[0]['id'];
        echo "2. Getting board details for ID: {$boardId}\n";
        $board = $client->boards()->get($boardId);
        echo "Board name: " . $board['name'] . "\n\n";
    }

    // Example 3: Create a new item with various column types
    echo "3. Creating a new item with complex column types...\n";
    
    $itemData = [
        'board_id' => $boardId ?? 1234567890, // Replace with your board ID
        'item_name' => 'SDK Test Item - ' . date('Y-m-d H:i:s'),
        'column_values' => [
            // Text column
            new TextColumn('text_column_id', 'This is a test item created via SDK'),
            
            // Status column
            new StatusColumn('status_column_id', 'Working', 'blue'),
            
            // Email column
            new EmailColumn('email_column_id', 'developer@example.com'),  // Includes both email and text fields
            
            // Phone column
            new PhoneColumn('phone_column_id', '+1-555-123-4567'),  // Includes both phone and text fields
            
            // Email column with custom display text
            new EmailColumn('email_custom_column_id', 'admin@example.com', 'Admin User'),
            
            // Phone column with custom display text
            new PhoneColumn('phone_custom_column_id', '+1-555-987-6543', 'Support Line'),
            
            // Timeline column (date range)
            new TimelineColumn('timeline_column_id', '2024-01-01', '2024-01-31'),
            
            // Number column (percentage)
            new NumberColumn('number_column_id', 75.5, 'percentage'),
            
            // Currency column
            new NumberColumn('currency_column_id', 1250.00, 'currency_USD'),
            
            // Location column - full address
            new LocationColumn('location_column_id', [
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'lat' => 40.7128,
                'lng' => -74.0060
            ]),
            
            // Location column - simple address
            new LocationColumn('simple_location_column_id', '456 Oak Ave, Los Angeles, CA')
        ]
    ];

    $newItem = $client->items()->create($itemData);
    echo "Created item with ID: " . $newItem['id'] . "\n\n";

    // Example 4: Update the item
    echo "4. Updating the created item...\n";
    $updateData = [
        'column_values' => [
            new StatusColumn('status_column_id', 'Done', 'green'),
            new NumberColumn('number_column_id', 100, 'percentage')
        ]
    ];

    $updatedItem = $client->items()->update($newItem['id'], $updateData);
    echo "Updated item successfully\n\n";

    // Example 5: Get items with pagination
    echo "5. Getting items with pagination...\n";
    $result = $client->items()->getAll($boardId ?? 1234567890, ['limit' => 10]);
    echo "Retrieved " . count($result['items']) . " items\n";
    echo "Has more pages: " . ($result['cursor'] ? 'Yes' : 'No') . "\n\n";

    // Example 6: Search items by column values
    echo "6. Searching items by column values...\n";
    $searchResults = $client->items()->searchByColumnValues(
        $boardId ?? 1234567890,
        ['text_column_id' => 'SDK Test Item']
    );
    echo "Found " . count($searchResults) . " matching items\n\n";

    // Example 7: Get board columns
    echo "7. Getting board columns...\n";
    $columns = $client->boards()->getColumns($boardId ?? 1234567890);
    echo "Board has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['title']} ({$column['type']})\n";
    }
    echo "\n";

    // Example 8: Get current user
    echo "8. Getting current user information...\n";
    $currentUser = $client->users()->getCurrent();
    echo "Current user: " . $currentUser['name'] . " (" . $currentUser['email'] . ")\n\n";

    // Example 9: Get workspace information
    echo "9. Getting workspace information...\n";
    $workspaces = $client->workspaces()->getAll();
    echo "Found " . count($workspaces) . " workspaces\n\n";

    // Example 9.5: Location column examples
    echo "9.5. Location column examples...\n";
    
    // Create an item with different location formats
    $locationItemData = [
        'board_id' => $boardId ?? 1234567890,
        'item_name' => 'Location Test Item - ' . date('Y-m-d H:i:s'),
        'column_values' => [
            // Full address with coordinates
            LocationColumn::withFullAddress(
                'location_full_column_id',
                '789 Business Blvd',
                'San Francisco',
                'CA',
                'USA',
                37.7749,
                -122.4194,
                'US'
            ),
            
            // Just coordinates
            LocationColumn::withCoordinates('location_coords_column_id', 34.0522, -118.2437),
            
            // City and state only
            LocationColumn::withCityState('location_city_state_column_id', 'Chicago', 'IL', 'USA'),
            
            // Simple address string
            LocationColumn::withAddress('location_simple_column_id', '321 Pine St, Seattle, WA')
        ]
    ];

    $locationItem = $client->items()->create($locationItemData);
    echo "Created location test item with ID: " . $locationItem['id'] . "\n";
    echo "Location examples created successfully\n\n";

    // Example 10: Custom GraphQL query
    echo "10. Executing custom GraphQL query...\n";
    $customQuery = <<<'GRAPHQL'
    query {
        me {
            id
            name
            email
            account {
                name
                slug
            }
        }
    }
    GRAPHQL;

    $customResult = $client->query($customQuery);
    echo "Account: " . $customResult['me']['account']['name'] . "\n\n";

    echo "=== Example completed successfully! ===\n";

} catch (MondayApiException $e) {
    echo "Monday.com API Error: " . $e->getMessage() . "\n";
    
    if ($e->isGraphQLError()) {
        $errors = $e->getGraphQLErrors();
        echo "GraphQL Errors:\n";
        foreach ($errors as $error) {
            echo "- " . $error['message'] . "\n";
        }
    }
    
    echo "Error Details: " . $e->getErrorDetails() . "\n";
    
} catch (RateLimitException $e) {
    echo "Rate Limit Error: " . $e->getMessage() . "\n";
    echo "Retry after: " . $e->getRetryAfter() . " seconds\n";
    
} catch (\Exception $e) {
    echo "Unexpected Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Example 11: Rate limiter usage statistics
echo "\n11. Rate limiter usage statistics:\n";
$usageStats = $client->getRateLimiter()->getUsageStats();
echo "Minute requests: {$usageStats['minute_requests']}/{$usageStats['minute_limit']}\n";
echo "Daily requests: {$usageStats['daily_requests']}/{$usageStats['daily_limit']}\n";
echo "Minute remaining: {$usageStats['minute_remaining']}\n";
echo "Daily remaining: {$usageStats['daily_remaining']}\n"; 