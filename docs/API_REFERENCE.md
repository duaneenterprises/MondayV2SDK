# Monday.com V2 SDK API Reference

## Table of Contents

1. [MondayClient](#mondayclient)
2. [Services](#services)
   - [BoardService](#boardservice)
   - [ItemService](#itemservice)
   - [ColumnService](#columnservice)
   - [UserService](#userservice)
   - [WorkspaceService](#workspaceservice)
3. [Column Types](#column-types)
4. [Exceptions](#exceptions)
5. [Configuration](#configuration)

## MondayClient

The main entry point for the Monday.com SDK.

### Constructor

```php
public function __construct(string $apiToken, array $config = [])
```

**Parameters:**
- `$apiToken` (string) - Your Monday.com API token
- `$config` (array) - Configuration options (see Configuration section)

**Example:**
```php
$client = new MondayClient('your-api-token', [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ]
]);
```

### Methods

#### `query(string $query, array $variables = []): array`
Execute a GraphQL query.

**Parameters:**
- `$query` (string) - GraphQL query string
- `$variables` (array) - Query variables

**Returns:** Array containing the response data

**Example:**
```php
$result = $client->query('
    query {
        boards {
            id
            name
            items {
                id
                name
            }
        }
    }
');
```

#### `mutate(string $mutation, array $variables = []): array`
Execute a GraphQL mutation.

**Parameters:**
- `$mutation` (string) - GraphQL mutation string
- `$variables` (array) - Mutation variables

**Returns:** Array containing the response data

**Example:**
```php
$result = $client->mutate('
    mutation ($boardId: ID!, $itemName: String!) {
        create_item(board_id: $boardId, item_name: $itemName) {
            id
            name
        }
    }
', [
    'boardId' => 1234567890,
    'itemName' => 'New Task'
]);
```

#### `boards(): BoardService`
Get the board service.

#### `items(): ItemService`
Get the item service.

#### `columns(): ColumnService`
Get the column service.

#### `users(): UserService`
Get the user service.

#### `workspaces(): WorkspaceService`
Get the workspace service.

#### `getHttpClient(): HttpClient`
Get the underlying HTTP client.

#### `getRateLimiter(): RateLimiter`
Get the rate limiter instance.

#### `getLogger(): Logger`
Get the logger instance.

## Services

### BoardService

Handles board-related operations.

#### `getAll(array $options = []): array`
Get all boards.

**Parameters:**
- `$options` (array) - Query options
  - `limit` (int) - Number of boards to return
  - `page` (int) - Page number
  - `ids` (array) - Specific board IDs

**Returns:** Array of boards

**Example:**
```php
$boards = $client->boards()->getAll([
    'limit' => 50,
    'ids' => [1234567890, 1234567891]
]);
```

#### `getById(int $boardId): array`
Get a specific board by ID.

**Parameters:**
- `$boardId` (int) - Board ID

**Returns:** Board data

**Example:**
```php
$board = $client->boards()->getById(1234567890);
```

#### `create(array $data): array`
Create a new board.

**Parameters:**
- `$data` (array) - Board data
  - `name` (string) - Board name
  - `board_kind` (string) - Board type (public, private, share)
  - `template_id` (int) - Template ID (optional)

**Returns:** Created board data

**Example:**
```php
$board = $client->boards()->create([
    'name' => 'New Project Board',
    'board_kind' => 'public'
]);
```

#### `update(int $boardId, array $data): array`
Update a board.

**Parameters:**
- `$boardId` (int) - Board ID
- `$data` (array) - Update data

**Returns:** Updated board data

**Example:**
```php
$board = $client->boards()->update(1234567890, [
    'name' => 'Updated Board Name'
]);
```

#### `delete(int $boardId): bool`
Delete a board.

**Parameters:**
- `$boardId` (int) - Board ID

**Returns:** True if successful

**Example:**
```php
$success = $client->boards()->delete(1234567890);
```

### ItemService

Handles item-related operations.

#### `getAll(int $boardId, array $options = []): array`
Get all items from a board with pagination.

**Parameters:**
- `$boardId` (int) - Board ID
- `$options` (array) - Query options
  - `limit` (int) - Number of items per page (default: 500)
  - `cursor` (string) - Pagination cursor
  - `query_params` (array) - Additional query parameters

**Returns:** Array with `items` and `cursor`

**Example:**
```php
$result = $client->items()->getAll(1234567890, [
    'limit' => 100
]);

$items = $result['items'];
$cursor = $result['cursor'];
```

#### `getNextPage(string $cursor): array`
Get the next page of items.

**Parameters:**
- `$cursor` (string) - Pagination cursor

**Returns:** Array with `items` and `cursor`

**Example:**
```php
$nextPage = $client->items()->getNextPage($cursor);
```

#### `getById(int $itemId): array`
Get a specific item by ID.

**Parameters:**
- `$itemId` (int) - Item ID

**Returns:** Item data

**Example:**
```php
$item = $client->items()->getById(1234567891);
```

#### `create(array $data): array`
Create a new item.

**Parameters:**
- `$data` (array) - Item data
  - `board_id` (int) - Board ID
  - `item_name` (string) - Item name
  - `column_values` (array) - Column values (optional)

**Returns:** Created item data

**Example:**
```php
$item = $client->items()->create([
    'board_id' => 1234567890,
    'item_name' => 'New Task',
    'column_values' => [
        new TextColumn('text_01', 'Task description'),
        new StatusColumn('status_01', 'Working', 'blue'),
        new EmailColumn('email_01', 'user@example.com', 'John Doe')
    ]
]);
```

#### `update(int $itemId, array $data): array`
Update an item.

**Parameters:**
- `$itemId` (int) - Item ID
- `$data` (array) - Update data

**Returns:** Updated item data

**Example:**
```php
$item = $client->items()->update(1234567891, [
    'item_name' => 'Updated Task Name',
    'column_values' => [
        new StatusColumn('status_01', 'Done', 'green')
    ]
]);
```

#### `delete(int $itemId): bool`
Delete an item.

**Parameters:**
- `$itemId` (int) - Item ID

**Returns:** True if successful

**Example:**
```php
$success = $client->items()->delete(1234567891);
```

#### `searchByColumnValues(int $boardId, array $columnValues): array`
Search for items by column values.

**Parameters:**
- `$boardId` (int) - Board ID
- `$columnValues` (array) - Column values to search for

**Returns:** Array of matching items

**Example:**
```php
$items = $client->items()->searchByColumnValues(1234567890, [
    'status_01' => 'Working',
    'priority_01' => 'High'
]);
```

#### `searchByMultipleColumns(int $boardId, array $columnValues): array`
Search for items by multiple column values.

**Parameters:**
- `$boardId` (int) - Board ID
- `$columnValues` (array) - Column values to search for

**Returns:** Array of matching items

**Example:**
```php
$items = $client->items()->searchByMultipleColumns(1234567890, [
    'status_01' => 'Working',
    'priority_01' => 'High'
]);
```

### ColumnService

Handles column-related operations.

#### `getAll(int $boardId): array`
Get all columns for a board.

**Parameters:**
- `$boardId` (int) - Board ID

**Returns:** Array of columns

**Example:**
```php
$columns = $client->columns()->getAll(1234567890);
```

#### `getById(int $columnId): array`
Get a specific column by ID.

**Parameters:**
- `$columnId` (int) - Column ID

**Returns:** Column data

**Example:**
```php
$column = $client->columns()->getById('text_01');
```

### UserService

Handles user-related operations.

#### `getAll(array $options = []): array`
Get all users.

**Parameters:**
- `$options` (array) - Query options

**Returns:** Array of users

**Example:**
```php
$users = $client->users()->getAll();
```

#### `getById(int $userId): array`
Get a specific user by ID.

**Parameters:**
- `$userId` (int) - User ID

**Returns:** User data

**Example:**
```php
$user = $client->users()->getById(1234567890);
```

#### `getMe(): array`
Get the current user's information.

**Returns:** Current user data

**Example:**
```php
$me = $client->users()->getMe();
```

### WorkspaceService

Handles workspace-related operations.

#### `getAll(array $options = []): array`
Get all workspaces.

**Parameters:**
- `$options` (array) - Query options

**Returns:** Array of workspaces

**Example:**
```php
$workspaces = $client->workspaces()->getAll();
```

#### `getById(int $workspaceId): array`
Get a specific workspace by ID.

**Parameters:**
- `$workspaceId` (int) - Workspace ID

**Returns:** Workspace data

**Example:**
```php
$workspace = $client->workspaces()->getById(1234567890);
```

## Column Types

### TextColumn

```php
new TextColumn(string $columnId, string $value)
```

**Example:**
```php
$textColumn = new TextColumn('text_01', 'Sample text');
```

### NumberColumn

```php
new NumberColumn(string $columnId, float $number, string $format = null)
```

**Example:**
```php
$numberColumn = new NumberColumn('number_01', 85.5, 'percentage');
```

### StatusColumn

```php
new StatusColumn(string $columnId, string $label, string $color = null)
```

**Example:**
```php
$statusColumn = new StatusColumn('status_01', 'Working', 'blue');
```

### EmailColumn

```php
new EmailColumn(string $columnId, string $email, string $text = null)
```

**Example:**
```php
$emailColumn = new EmailColumn('email_01', 'user@example.com', 'John Doe');
```

### PhoneColumn

```php
new PhoneColumn(string $columnId, string $phone, string $text = null)
```

**Example:**
```php
$phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');
```

### LocationColumn

```php
new LocationColumn(string $columnId, array|string $location)
```

**Example:**
```php
$locationColumn = new LocationColumn('location_01', [
    'address' => '123 Main St',
    'city' => 'New York',
    'state' => 'NY',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060
]);
```

### TimelineColumn

```php
new TimelineColumn(string $columnId, string $startDate, string $endDate = null)
```

**Example:**
```php
$timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
```

## Exceptions

### MondayApiException

Thrown when the Monday.com API returns an error.

**Methods:**
- `getErrorDetails(): array` - Get detailed error information
- `isGraphQLError(): bool` - Check if it's a GraphQL error
- `getGraphQLErrors(): array` - Get GraphQL errors

### RateLimitException

Thrown when rate limits are exceeded.

**Methods:**
- `getRetryAfter(): int` - Get seconds to wait before retrying
- `getRetryAfterDateTime(): DateTime` - Get retry time as DateTime
- `canRetry(DateTime $lastAttempt): bool` - Check if enough time has passed

## Configuration

### Available Options

```php
$config = [
    'timeout' => 30,                    // HTTP timeout in seconds
    'rate_limit' => [
        'minute_limit' => 100,          // Requests per minute
        'daily_limit' => 1000           // Requests per day
    ],
    'logging' => [
        'level' => 'info',              // Log level (debug, info, warning, error)
        'enabled' => true,              // Enable/disable logging
        'file' => '/path/to/logs/monday.log'  // Log file path
    ]
];
```

### Log Levels

- `debug` - Detailed debugging information
- `info` - General information
- `warning` - Warning messages
- `error` - Error messages only

### Rate Limiting

The SDK includes built-in rate limiting to prevent API quota exhaustion:

- **Minute Limit**: Maximum requests per minute
- **Daily Limit**: Maximum requests per day
- **Automatic Retry**: Failed requests due to rate limits are automatically retried
- **Exponential Backoff**: Retry delays increase exponentially

### Error Handling Best Practices

```php
try {
    $result = $client->items()->create($itemData);
} catch (RateLimitException $e) {
    // Handle rate limiting
    $retryAfter = $e->getRetryAfter();
    sleep($retryAfter);
    // Retry the request
} catch (MondayApiException $e) {
    if ($e->isGraphQLError()) {
        $errors = $e->getGraphQLErrors();
        // Handle GraphQL errors
    }
    // Handle other API errors
} catch (Exception $e) {
    // Handle unexpected errors
}
``` 