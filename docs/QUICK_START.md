# Monday.com PHP SDK - Quick Start Guide

Get up and running with the Monday.com PHP SDK in minutes!

## Prerequisites

- PHP 8.0 or higher
- Composer
- Monday.com API token

## Installation

### Via Composer (Recommended)

```bash
composer require duaneenterprises/monday-v2-sdk
```

### Manual Installation

1. Download the SDK files
2. Include the autoloader in your project
3. Add the SDK to your namespace

## Get Your API Token

1. Go to [Monday.com](https://monday.com)
2. Navigate to **Admin** → **API**
3. Copy your API token

## Basic Usage

### 1. Initialize the Client

```php
<?php

require_once 'vendor/autoload.php';

use MondayV2SDK\MondayClient;

// Initialize with your API token
$client = new MondayClient('your-api-token-here');
```

### 2. Create Your First Item

```php
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;

// Create a simple item
$item = $client->items()->create([
    'board_id' => 1234567890,  // Replace with your board ID
    'item_name' => 'My First Task',
    'column_values' => [
        new TextColumn('text_01', 'This is my first task description'),
        new StatusColumn('status_01', 'Working', 'blue')
    ]
]);

echo "Created item: " . $item['name'] . " (ID: " . $item['id'] . ")";
```

### 3. Get Items from a Board

```php
// Get all items from a board
$result = $client->items()->getAll(1234567890);  // Replace with your board ID

foreach ($result['items'] as $item) {
    echo "Item: " . $item['name'] . "\n";
}
```

### 4. Search for Items

```php
// Search for items with specific status
$items = $client->items()->searchByColumnValues(1234567890, [
    'status_01' => 'Working'
]);

echo "Found " . count($items) . " items with 'Working' status\n";
```

## Common Column Types

### Text Column
```php
new TextColumn('text_01', 'Your text here');
```

### Status Column
```php
new StatusColumn('status_01', 'Working', 'blue');
```

### Email Column
```php
new EmailColumn('email_01', 'user@example.com', 'John Doe');
```

### Number Column
```php
new NumberColumn('number_01', 42, 'currency');  // currency, percentage, duration
```

### Timeline Column
```php
new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
```

## Error Handling

```php
try {
    $item = $client->items()->create([
        'board_id' => 1234567890,
        'item_name' => 'Test Item'
    ]);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Complete Example

```php
<?php

require_once 'vendor/autoload.php';

use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;

// Initialize client
$client = new MondayClient('your-api-token-here');

// Board ID (replace with your actual board ID)
$boardId = 1234567890;

try {
    // Create a comprehensive item
    $item = $client->items()->create([
        'board_id' => $boardId,
        'item_name' => 'Website Redesign Project',
        'column_values' => [
            new TextColumn('description_01', 'Complete redesign of company website'),
            new StatusColumn('status_01', 'Working', 'blue'),
            new EmailColumn('client_01', 'client@example.com', 'Client Contact'),
            new NumberColumn('budget_01', 15000, 'currency'),
            new TimelineColumn('deadline_01', '2024-03-31')
        ]
    ]);
    
    echo "✅ Created item: " . $item['name'] . "\n";
    
    // Get all items
    $result = $client->items()->getAll($boardId);
    echo "📋 Total items in board: " . count($result['items']) . "\n";
    
    // Search for working items
    $workingItems = $client->items()->searchByColumnValues($boardId, [
        'status_01' => 'Working'
    ]);
    echo "🔧 Items with 'Working' status: " . count($workingItems) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

## Next Steps

1. **Find Your Board ID**: Use the Monday.com interface to get your board ID
2. **Explore Column Types**: Check out the [Column Types Documentation](COLUMN_TYPES.md)
3. **Read Examples**: See [Comprehensive Examples](EXAMPLES.md)
4. **API Reference**: Consult the [API Reference](API_REFERENCE.md)

## Troubleshooting

### Common Issues

**"Invalid API token"**
- Verify your API token is correct
- Ensure the token has the necessary permissions

**"Board not found"**
- Check that the board ID is correct
- Ensure your API token has access to the board

**"Column not found"**
- Verify the column ID exists in your board
- Column IDs are usually in format like `text_01`, `status_01`, etc.

### Getting Help

- Check the [API Reference](API_REFERENCE.md) for detailed method documentation
- Review [Examples](EXAMPLES.md) for common use cases
- Ensure you're using the latest version of the SDK

## Configuration Options

```php
$client = new MondayClient('your-api-token', [
    'timeout' => 30,                    // HTTP timeout
    'rate_limit' => [
        'minute_limit' => 100,          // Requests per minute
        'daily_limit' => 1000           // Requests per day
    ],
    'logging' => [
        'level' => 'info',              // Log level
        'enabled' => true,              // Enable logging
        'file' => '/path/to/logs/monday.log'
    ]
]);
```

That's it! You're now ready to integrate Monday.com into your PHP application. 🚀 