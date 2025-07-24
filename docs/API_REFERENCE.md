# API Reference

## MondayClient

The main client class for interacting with Monday.com API.

### Constructor

```php
new MondayClient(string $apiToken, array $config = [])
```

**Parameters:**
- `$apiToken` (string): Your Monday.com API token
- `$config` (array): Configuration options (optional)

**Configuration Options:**
```php
[
    'timeout' => 30,                    // HTTP timeout in seconds
    'rate_limit' => [
        'minute_limit' => 100,          // Requests per minute
        'daily_limit' => 1000           // Requests per day
    ],
    'logging' => [
        'level' => 'debug',             // Log level: debug, info, warning, error
        'enabled' => true               // Enable/disable logging
    ]
]
```

### Methods

#### `items()`
Returns the ItemService for managing items.

#### `boards()`
Returns the BoardService for managing boards.

#### `columns()`
Returns the ColumnService for managing columns.

#### `users()`
Returns the UserService for managing users.

#### `workspaces()`
Returns the WorkspaceService for managing workspaces.

#### `query(string $query, array $variables = [])`
Execute a custom GraphQL query.

#### `mutate(string $mutation, array $variables = [])`
Execute a custom GraphQL mutation.

## ItemService

### Methods

#### `create(array $data)`
Create a new item.

**Parameters:**
```php
[
    'board_id' => int,                  // Required: Board ID
    'item_name' => string,              // Required: Item name
    'column_values' => array            // Optional: Array of ColumnType objects
]
```

**Returns:** `array` - Created item data

#### `update(int $itemId, array $data)`
Update an existing item.

**Parameters:**
```php
[
    'column_values' => array            // Array of ColumnType objects
]
```

**Returns:** `array` - Updated item data

#### `delete(int $itemId)`
Delete an item.

**Returns:** `array` - Deletion result

#### `get(int $itemId)`
Get a single item by ID.

**Returns:** `array` - Item data

#### `getAll(int $boardId, array $options = [])`
Get all items from a board with pagination.

**Options:**
```php
[
    'limit' => int,                     // Number of items per page (default: 50)
    'cursor' => string,                 // Pagination cursor
    'order_by' => string,               // Sort order
    'state' => string                   // Item state: active, archived, deleted
]
```

**Returns:** `array` - `['items' => array, 'cursor' => string]`

#### `getNextPage(string $cursor, array $options = [])`
Get the next page of items.

**Returns:** `array` - `['items' => array, 'cursor' => string]`

#### `searchByColumnValues(int $boardId, array $columnValues, array $options = [])`
Search items by column values.

**Parameters:**
- `$boardId` (int): Board ID
- `$columnValues` (array): Column ID => value pairs
- `$options` (array): Search options

**Returns:** `array` - Matching items

## BoardService

### Methods

#### `create(array $data)`
Create a new board.

**Parameters:**
```php
[
    'board_name' => string,             // Required: Board name
    'board_kind' => string,             // Optional: public, private, share
    'workspace_id' => int               // Optional: Workspace ID
]
```

#### `get(int $boardId)`
Get a board by ID.

#### `getAll(array $options = [])`
Get all boards.

#### `update(int $boardId, array $data)`
Update a board.

#### `delete(int $boardId)`
Delete a board.

#### `getColumns(int $boardId)`
Get all columns in a board.

#### `getSubscribers(int $boardId)`
Get board subscribers.

## ColumnService

### Methods

#### `create(int $boardId, array $data)`
Create a new column.

**Parameters:**
```php
[
    'title' => string,                  // Required: Column title
    'column_type' => string,            // Required: Column type
    'defaults' => array                 // Optional: Default values
]
```

#### `get(int $columnId)`
Get a column by ID.

#### `getAll(int $boardId)`
Get all columns in a board.

#### `update(int $columnId, array $data)`
Update a column.

#### `delete(int $columnId)`
Delete a column.

## UserService

### Methods

#### `get(int $userId)`
Get a user by ID.

#### `getAll(array $options = [])`
Get all users.

#### `getCurrent()`
Get the current user.

#### `getByBoard(int $boardId)`
Get users by board.

#### `search(string $searchTerm, array $options = [])`
Search users.

## WorkspaceService

### Methods

#### `get(int $workspaceId)`
Get a workspace by ID.

#### `getAll(array $options = [])`
Get all workspaces.

#### `getBoards(int $workspaceId, array $options = [])`
Get boards in a workspace.

#### `getSubscribers(int $workspaceId)`
Get workspace subscribers.

#### `getOwners(int $workspaceId)`
Get workspace owners.

## Column Types

### TextColumn
```php
new TextColumn(string $columnId, string $value)
```

### StatusColumn
```php
new StatusColumn(string $columnId, string $text, string $color)
```
**Colors:** red, green, blue, yellow, orange, purple, pink, gray

### EmailColumn
```php
new EmailColumn(string $columnId, string $email, string $text)
```

### PhoneColumn
```php
new PhoneColumn(string $columnId, string $phone, string $text)
```

### NumberColumn
```php
new NumberColumn(string $columnId, float|int $value, string $format)
```
**Formats:** number, currency, percent

### TimelineColumn
```php
new TimelineColumn(string $columnId, string $startDate, string $endDate = null)
```

### LocationColumn
```php
new LocationColumn(string $columnId, array $location)
```
**Location array:**
```php
[
    'address' => string,
    'city' => string,
    'country' => string,
    'lat' => float,
    'lng' => float
]
```

## Exceptions

### MondayApiException
Thrown when the Monday.com API returns an error.

**Methods:**
- `getMessage()`: Error message
- `getCode()`: Error code
- `getErrors()`: Array of GraphQL errors

### RateLimitException
Thrown when rate limit is exceeded.

**Methods:**
- `getMessage()`: Error message
- `getRetryAfter()`: Seconds to wait before retrying

## Error Handling

```php
try {
    $result = $client->items()->create($data);
} catch (MondayApiException $e) {
    // Handle API errors
    echo "API Error: " . $e->getMessage();
    $errors = $e->getErrors();
} catch (RateLimitException $e) {
    // Handle rate limiting
    $retryAfter = $e->getRetryAfter();
    sleep($retryAfter);
} catch (Exception $e) {
    // Handle other errors
    echo "Unexpected error: " . $e->getMessage();
}
```

## Rate Limiting

The SDK automatically handles rate limiting with configurable limits:

```php
$client = new MondayClient('token', [
    'rate_limit' => [
        'minute_limit' => 100,  // Requests per minute
        'daily_limit' => 1000   // Requests per day
    ]
]);
```

## Logging

Enable logging for debugging:

```php
$client = new MondayClient('token', [
    'logging' => [
        'level' => 'debug',     // debug, info, warning, error
        'enabled' => true
    ]
]);
```

## Pagination

The SDK uses cursor-based pagination:

```php
$result = $client->items()->getAll($boardId, ['limit' => 50]);
$items = $result['items'];
$cursor = $result['cursor'];

if ($cursor) {
    $nextPage = $client->items()->getNextPage($cursor);
}
``` 