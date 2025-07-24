# Quick Start Guide

## Installation

```bash
composer require duaneenterprises/monday-v2-sdk
```

## Basic Usage

### 1. Initialize the Client

```php
use MondayV2SDK\MondayClient;

$client = new MondayClient('your-api-token');
```

### 2. Create an Item

```php
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;

$item = $client->items()->create([
    'board_id' => 1234567890,
    'item_name' => 'New Task',
    'column_values' => [
        new TextColumn('text_column_id', 'Task description'),
        new StatusColumn('status_column_id', 'Working', 'blue'),
        new EmailColumn('email_column_id', 'user@example.com', 'John Doe')
    ]
]);
```

### 3. Get All Items

```php
$result = $client->items()->getAll(1234567890, ['limit' => 100]);
$items = $result['items'];
$cursor = $result['cursor'];

// Get next page
if ($cursor) {
    $nextPage = $client->items()->getNextPage($cursor);
}
```

### 4. Search Items

```php
$items = $client->items()->searchByColumnValues(1234567890, [
    'status_column_id' => 'Working'
]);
```

### 5. Update an Item

```php
$updatedItem = $client->items()->update(1234567891, [
    'column_values' => [
        new StatusColumn('status_column_id', 'Done', 'green')
    ]
]);
```

### 6. Delete an Item

```php
$client->items()->delete(1234567891);
```

## Column Types

### Text Column
```php
new TextColumn('column_id', 'text value')
```

### Status Column
```php
new StatusColumn('column_id', 'status text', 'color')
// Colors: red, green, blue, yellow, orange, purple, pink, gray
```

### Email Column
```php
new EmailColumn('column_id', 'email@example.com', 'display name')
```

### Phone Column
```php
new PhoneColumn('column_id', '+1-555-123-4567', 'display name')
```

### Number Column
```php
new NumberColumn('column_id', 123.45, 'currency') // or 'number', 'percent'
```

### Timeline Column
```php
new TimelineColumn('column_id', '2024-01-01', '2024-01-31')
```

### Location Column
```php
new LocationColumn('column_id', [
    'address' => '123 Main St',
    'city' => 'New York',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060
])
```

## Error Handling

```php
try {
    $item = $client->items()->create($data);
} catch (MondayApiException $e) {
    echo "API Error: " . $e->getMessage();
} catch (RateLimitException $e) {
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds";
}
```

## Configuration

```php
$client = new MondayClient('api-token', [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ],
    'logging' => [
        'level' => 'debug',
        'enabled' => true
    ]
]);
``` 