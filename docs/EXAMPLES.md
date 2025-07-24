# Monday.com PHP SDK Examples

This document provides comprehensive examples of how to use the Monday.com PHP SDK for various common scenarios.

## Table of Contents

1. [Basic Setup](#basic-setup)
2. [Board Management](#board-management)
3. [Item Management](#item-management)
4. [Column Types](#column-types)
5. [Search and Filtering](#search-and-filtering)
6. [Pagination](#pagination)
7. [Error Handling](#error-handling)
8. [Advanced Patterns](#advanced-patterns)
9. [Real-World Scenarios](#real-world-scenarios)

## Basic Setup

### Initialize the Client

```php
<?php

use MondayV2SDK\MondayClient;

// Basic initialization
$client = new MondayClient('your-api-token');

// With configuration
$client = new MondayClient('your-api-token', [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ],
    'logging' => [
        'level' => 'info',
        'enabled' => true,
        'file' => '/path/to/logs/monday.log'
    ]
]);
```

### Get API Information

```php
// Get current user information
$me = $client->users()->getMe();
echo "Logged in as: " . $me['name'];

// Get all workspaces
$workspaces = $client->workspaces()->getAll();
foreach ($workspaces as $workspace) {
    echo "Workspace: " . $workspace['name'] . "\n";
}
```

## Board Management

### Get All Boards

```php
// Get all boards
$boards = $client->boards()->getAll();

// Get specific boards
$boards = $client->boards()->getAll([
    'ids' => [1234567890, 1234567891]
]);

// Get boards with limit
$boards = $client->boards()->getAll([
    'limit' => 50
]);
```

### Create a New Board

```php
// Create a simple board
$board = $client->boards()->create([
    'name' => 'Project Management',
    'board_kind' => 'public'
]);

// Create a board from template
$board = $client->boards()->create([
    'name' => 'Task Tracker',
    'board_kind' => 'private',
    'template_id' => 12345
]);

echo "Created board: " . $board['name'] . " (ID: " . $board['id'] . ")";
```

### Update and Delete Boards

```php
$boardId = 1234567890;

// Update board
$updatedBoard = $client->boards()->update($boardId, [
    'name' => 'Updated Project Board'
]);

// Delete board
$success = $client->boards()->delete($boardId);
if ($success) {
    echo "Board deleted successfully";
}
```

## Item Management

### Create Items

```php
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;

$boardId = 1234567890;

// Create a simple item
$item = $client->items()->create([
    'board_id' => $boardId,
    'item_name' => 'New Task'
]);

// Create item with column values
$item = $client->items()->create([
    'board_id' => $boardId,
    'item_name' => 'Complete API Integration',
    'column_values' => [
        new TextColumn('text_01', 'Integrate Monday.com API with our CRM system'),
        new StatusColumn('status_01', 'Working', 'blue'),
        new EmailColumn('email_01', 'developer@example.com', 'John Developer')
    ]
]);
```

### Update Items

```php
$itemId = 1234567891;

// Update item name
$updatedItem = $client->items()->update($itemId, [
    'item_name' => 'Updated Task Name'
]);

// Update column values
$updatedItem = $client->items()->update($itemId, [
    'column_values' => [
        new StatusColumn('status_01', 'Done', 'green'),
        new TextColumn('text_01', 'Task completed successfully')
    ]
]);
```

### Get Items

```php
$boardId = 1234567890;

// Get a specific item
$item = $client->items()->getById(1234567891);

// Get all items from a board
$result = $client->items()->getAll($boardId);
$items = $result['items'];

foreach ($items as $item) {
    echo "Item: " . $item['name'] . " (ID: " . $item['id'] . ")\n";
}
```

### Delete Items

```php
$itemId = 1234567891;
$success = $client->items()->delete($itemId);

if ($success) {
    echo "Item deleted successfully";
}
```

## Column Types

### Text Columns

```php
use MondayV2SDK\ColumnTypes\TextColumn;

// Simple text
$textColumn = new TextColumn('text_01', 'This is a sample text');

// Long description
$descriptionColumn = new TextColumn('description_01', 'This is a detailed description of the task that can span multiple lines and contain important information.');
```

### Status Columns

```php
use MondayV2SDK\ColumnTypes\StatusColumn;

// Status with color
$statusColumn = new StatusColumn('status_01', 'Working', 'blue');

// Common status values
$statuses = [
    new StatusColumn('status_01', 'Working', 'blue'),
    new StatusColumn('status_01', 'Done', 'green'),
    new StatusColumn('status_01', 'Stuck', 'red'),
    new StatusColumn('status_01', 'Review', 'yellow')
];
```

### Email and Phone Columns

```php
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\PhoneColumn;

// Email with display text
$emailColumn = new EmailColumn('email_01', 'john.doe@example.com', 'John Doe');

// Phone with display text
$phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');

// Email without custom text (uses email as display text)
$emailColumn = new EmailColumn('email_01', 'contact@example.com');
```

### Number Columns

```php
use MondayV2SDK\ColumnTypes\NumberColumn;

// Simple number
$numberColumn = new NumberColumn('number_01', 42);

// Percentage
$percentageColumn = new NumberColumn('percentage_01', 85.5, 'percentage');

// Currency
$priceColumn = new NumberColumn('price_01', 99.99, 'currency');

// Duration (in hours)
$durationColumn = new NumberColumn('duration_01', 8.5, 'duration');
```

### Timeline Columns

```php
use MondayV2SDK\ColumnTypes\TimelineColumn;

// Date range
$timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');

// Single date (same start and end)
$singleDateColumn = new TimelineColumn('timeline_01', '2024-01-15');

// Project timeline
$projectTimeline = new TimelineColumn('project_timeline_01', '2024-01-01', '2024-03-31');
```

### Location Columns

```php
use MondayV2SDK\ColumnTypes\LocationColumn;

// Full address with coordinates
$locationColumn = new LocationColumn('location_01', [
    'address' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060
]);

// Simple address string
$simpleLocation = new LocationColumn('location_01', '123 Main St, New York, NY 10001');

// Just city and state
$cityLocation = new LocationColumn('location_01', [
    'city' => 'San Francisco',
    'state' => 'CA',
    'country' => 'USA'
]);
```

### Complex Item Creation

```php
// Create an item with multiple column types
$item = $client->items()->create([
    'board_id' => 1234567890,
    'item_name' => 'Website Redesign Project',
    'column_values' => [
        new TextColumn('description_01', 'Complete redesign of company website with modern UI/UX'),
        new StatusColumn('status_01', 'In Progress', 'blue'),
        new EmailColumn('client_email_01', 'client@example.com', 'Client Contact'),
        new PhoneColumn('client_phone_01', '+1-555-987-6543', 'Client Phone'),
        new NumberColumn('budget_01', 15000, 'currency'),
        new NumberColumn('progress_01', 65, 'percentage'),
        new TimelineColumn('deadline_01', '2024-03-31'),
        new LocationColumn('office_01', [
            'address' => '456 Business Ave',
            'city' => 'San Francisco',
            'state' => 'CA',
            'country' => 'USA'
        ])
    ]
]);
```

## Search and Filtering

### Search by Single Column

```php
$boardId = 1234567890;

// Search for items with specific status
$items = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Working'
]);

// Search for items with specific email
$items = $client->items()->searchByColumnValues($boardId, [
    'email_01' => 'john@example.com'
]);
```

### Search by Multiple Columns

```php
// Search for items matching multiple criteria
$items = $client->items()->searchByMultipleColumns($boardId, [
    'status_01' => 'Working',
    'priority_01' => 'High',
    'department_01' => 'Engineering'
]);

foreach ($items as $item) {
    echo "Found item: " . $item['name'] . "\n";
}
```

### Advanced Search Patterns

```php
// Search for items in a specific date range
$items = $client->items()->searchByColumnValues($boardId, [
    'timeline_01' => '2024-01-01'
]);

// Search for items with specific budget range
$items = $client->items()->searchByColumnValues($boardId, [
    'budget_01' => '10000'
]);
```

## Pagination

### Basic Pagination

```php
$boardId = 1234567890;
$allItems = [];

// Get first page
$result = $client->items()->getAll($boardId, ['limit' => 100]);
$allItems = array_merge($allItems, $result['items']);

// Get subsequent pages
while ($result['cursor']) {
    $result = $client->items()->getNextPage($result['cursor']);
    $allItems = array_merge($allItems, $result['items']);
}

echo "Total items: " . count($allItems);
```

### Pagination with Progress Tracking

```php
$boardId = 1234567890;
$allItems = [];
$page = 1;

$result = $client->items()->getAll($boardId, ['limit' => 50]);

do {
    echo "Processing page " . $page . " (" . count($result['items']) . " items)\n";
    $allItems = array_merge($allItems, $result['items']);
    
    if ($result['cursor']) {
        $result = $client->items()->getNextPage($result['cursor']);
        $page++;
    }
} while ($result['cursor']);

echo "Total items processed: " . count($allItems);
```

## Error Handling

### Basic Error Handling

```php
try {
    $item = $client->items()->create([
        'board_id' => 1234567890,
        'item_name' => 'Test Item'
    ]);
    echo "Item created successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Advanced Error Handling

```php
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;

try {
    $item = $client->items()->create($itemData);
} catch (RateLimitException $e) {
    // Handle rate limiting
    $retryAfter = $e->getRetryAfter();
    echo "Rate limit exceeded. Retry after {$retryAfter} seconds.\n";
    
    // Wait and retry
    sleep($retryAfter);
    $item = $client->items()->create($itemData);
} catch (MondayApiException $e) {
    if ($e->isGraphQLError()) {
        $errors = $e->getGraphQLErrors();
        foreach ($errors as $error) {
            echo "GraphQL Error: " . $error['message'] . "\n";
        }
    } else {
        echo "API Error: " . $e->getMessage() . "\n";
        echo "Details: " . json_encode($e->getErrorDetails()) . "\n";
    }
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}
```

### Retry Logic

```php
function createItemWithRetry($client, $itemData, $maxRetries = 3) {
    $attempts = 0;
    
    while ($attempts < $maxRetries) {
        try {
            return $client->items()->create($itemData);
        } catch (RateLimitException $e) {
            $attempts++;
            if ($attempts >= $maxRetries) {
                throw $e;
            }
            
            $retryAfter = $e->getRetryAfter();
            echo "Rate limited. Waiting {$retryAfter} seconds before retry {$attempts}...\n";
            sleep($retryAfter);
        } catch (MondayApiException $e) {
            // Don't retry on API errors
            throw $e;
        }
    }
}

// Usage
$item = createItemWithRetry($client, $itemData);
```

## Advanced Patterns

### Batch Operations

```php
function createMultipleItems($client, $boardId, $items) {
    $createdItems = [];
    $errors = [];
    
    foreach ($items as $itemData) {
        try {
            $itemData['board_id'] = $boardId;
            $item = $client->items()->create($itemData);
            $createdItems[] = $item;
        } catch (Exception $e) {
            $errors[] = [
                'item' => $itemData,
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'created' => $createdItems,
        'errors' => $errors
    ];
}

// Usage
$items = [
    ['item_name' => 'Task 1', 'column_values' => [new StatusColumn('status_01', 'Working')]],
    ['item_name' => 'Task 2', 'column_values' => [new StatusColumn('status_01', 'Done')]],
    ['item_name' => 'Task 3', 'column_values' => [new StatusColumn('status_01', 'Review')]]
];

$result = createMultipleItems($client, 1234567890, $items);
echo "Created: " . count($result['created']) . " items\n";
echo "Errors: " . count($result['errors']) . " items\n";
```

### Data Synchronization

```php
function syncItemsFromExternalSource($client, $boardId, $externalData) {
    // Get existing items
    $existingItems = [];
    $result = $client->items()->getAll($boardId);
    
    foreach ($result['items'] as $item) {
        $existingItems[$item['name']] = $item['id'];
    }
    
    // Process external data
    foreach ($externalData as $data) {
        $itemName = $data['name'];
        
        if (isset($existingItems[$itemName])) {
            // Update existing item
            $client->items()->update($existingItems[$itemName], [
                'column_values' => $data['column_values']
            ]);
        } else {
            // Create new item
            $client->items()->create([
                'board_id' => $boardId,
                'item_name' => $itemName,
                'column_values' => $data['column_values']
            ]);
        }
    }
}
```

### Custom GraphQL Queries

```php
// Execute custom GraphQL query
$result = $client->query('
    query ($boardId: ID!) {
        boards(ids: [$boardId]) {
            id
            name
            items_page(limit: 100) {
                cursor
                items {
                    id
                    name
                    column_values {
                        id
                        value
                        text
                        type
                    }
                }
            }
        }
    }
', [
    'boardId' => 1234567890
]);

// Execute custom GraphQL mutation
$result = $client->mutate('
    mutation ($boardId: ID!, $itemName: String!, $columnValues: JSON!) {
        create_item(
            board_id: $boardId,
            item_name: $itemName,
            column_values: $columnValues
        ) {
            id
            name
            state
            created_at
        }
    }
', [
    'boardId' => 1234567890,
    'itemName' => 'Custom Item',
    'columnValues' => json_encode([
        'status_01' => json_encode(['labels' => ['Working']])
    ])
]);
```

## Real-World Scenarios

### Project Management System

```php
class ProjectManager {
    private $client;
    private $boardId;
    
    public function __construct($apiToken, $boardId) {
        $this->client = new MondayClient($apiToken);
        $this->boardId = $boardId;
    }
    
    public function createProject($name, $description, $deadline, $assignee) {
        return $this->client->items()->create([
            'board_id' => $this->boardId,
            'item_name' => $name,
            'column_values' => [
                new TextColumn('description_01', $description),
                new StatusColumn('status_01', 'Planning', 'yellow'),
                new TimelineColumn('deadline_01', $deadline),
                new EmailColumn('assignee_01', $assignee['email'], $assignee['name'])
            ]
        ]);
    }
    
    public function updateProjectStatus($itemId, $status) {
        return $this->client->items()->update($itemId, [
            'column_values' => [
                new StatusColumn('status_01', $status)
            ]
        ]);
    }
    
    public function getActiveProjects() {
        return $this->client->items()->searchByColumnValues($this->boardId, [
            'status_01' => 'Working'
        ]);
    }
    
    public function getOverdueProjects() {
        $today = date('Y-m-d');
        return $this->client->items()->searchByColumnValues($this->boardId, [
            'deadline_01' => $today
        ]);
    }
}

// Usage
$projectManager = new ProjectManager('your-api-token', 1234567890);

// Create a new project
$project = $projectManager->createProject(
    'Website Redesign',
    'Complete redesign of company website',
    '2024-03-31',
    ['name' => 'John Doe', 'email' => 'john@example.com']
);

// Update project status
$projectManager->updateProjectStatus($project['id'], 'In Progress');

// Get active projects
$activeProjects = $projectManager->getActiveProjects();
```

### Customer Support System

```php
class SupportTicketManager {
    private $client;
    private $boardId;
    
    public function __construct($apiToken, $boardId) {
        $this->client = new MondayClient($apiToken);
        $this->boardId = $boardId;
    }
    
    public function createTicket($subject, $description, $customer, $priority) {
        return $this->client->items()->create([
            'board_id' => $this->boardId,
            'item_name' => $subject,
            'column_values' => [
                new TextColumn('description_01', $description),
                new StatusColumn('status_01', 'New', 'red'),
                new EmailColumn('customer_01', $customer['email'], $customer['name']),
                new PhoneColumn('phone_01', $customer['phone'], $customer['name']),
                new StatusColumn('priority_01', $priority, 'red')
            ]
        ]);
    }
    
    public function assignTicket($itemId, $agent) {
        return $this->client->items()->update($itemId, [
            'column_values' => [
                new EmailColumn('assigned_to_01', $agent['email'], $agent['name']),
                new StatusColumn('status_01', 'Assigned', 'blue')
            ]
        ]);
    }
    
    public function resolveTicket($itemId, $resolution) {
        return $this->client->items()->update($itemId, [
            'column_values' => [
                new TextColumn('resolution_01', $resolution),
                new StatusColumn('status_01', 'Resolved', 'green')
            ]
        ]);
    }
    
    public function getOpenTickets() {
        return $this->client->items()->searchByColumnValues($this->boardId, [
            'status_01' => 'New'
        ]);
    }
    
    public function getHighPriorityTickets() {
        return $this->client->items()->searchByMultipleColumns($this->boardId, [
            'priority_01' => 'High',
            'status_01' => 'New'
        ]);
    }
}

// Usage
$supportManager = new SupportTicketManager('your-api-token', 1234567890);

// Create a support ticket
$ticket = $supportManager->createTicket(
    'Login Issue',
    'Cannot log into the application',
    [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'phone' => '+1-555-123-4567'
    ],
    'High'
);

// Assign ticket to agent
$supportManager->assignTicket($ticket['id'], [
    'name' => 'Support Agent',
    'email' => 'support@company.com'
]);

// Resolve ticket
$supportManager->resolveTicket($ticket['id'], 'Password reset completed successfully');
```

### Sales Pipeline Management

```php
class SalesPipelineManager {
    private $client;
    private $boardId;
    
    public function __construct($apiToken, $boardId) {
        $this->client = new MondayClient($apiToken);
        $this->boardId = $boardId;
    }
    
    public function createLead($company, $contact, $value) {
        return $this->client->items()->create([
            'board_id' => $this->boardId,
            'item_name' => $company,
            'column_values' => [
                new TextColumn('contact_name_01', $contact['name']),
                new EmailColumn('contact_email_01', $contact['email'], $contact['name']),
                new PhoneColumn('contact_phone_01', $contact['phone'], $contact['name']),
                new NumberColumn('deal_value_01', $value, 'currency'),
                new StatusColumn('stage_01', 'Lead', 'yellow'),
                new TimelineColumn('expected_close_01', date('Y-m-d', strtotime('+30 days')))
            ]
        ]);
    }
    
    public function moveToNextStage($itemId, $stage) {
        return $this->client->items()->update($itemId, [
            'column_values' => [
                new StatusColumn('stage_01', $stage)
            ]
        ]);
    }
    
    public function updateDealValue($itemId, $value) {
        return $this->client->items()->update($itemId, [
            'column_values' => [
                new NumberColumn('deal_value_01', $value, 'currency')
            ]
        ]);
    }
    
    public function getLeads() {
        return $this->client->items()->searchByColumnValues($this->boardId, [
            'stage_01' => 'Lead'
        ]);
    }
    
    public function getWonDeals() {
        return $this->client->items()->searchByColumnValues($this->boardId, [
            'stage_01' => 'Won'
        ]);
    }
    
    public function getTotalPipelineValue() {
        $result = $this->client->items()->getAll($this->boardId);
        $total = 0;
        
        foreach ($result['items'] as $item) {
            foreach ($item['column_values'] as $column) {
                if ($column['id'] === 'deal_value_01' && $column['value']) {
                    $value = json_decode($column['value'], true);
                    $total += $value['number'] ?? 0;
                }
            }
        }
        
        return $total;
    }
}

// Usage
$salesManager = new SalesPipelineManager('your-api-token', 1234567890);

// Create a new lead
$lead = $salesManager->createLead(
    'Acme Corporation',
    [
        'name' => 'John Smith',
        'email' => 'john.smith@acme.com',
        'phone' => '+1-555-987-6543'
    ],
    50000
);

// Move lead to next stage
$salesManager->moveToNextStage($lead['id'], 'Qualified');

// Update deal value
$salesManager->updateDealValue($lead['id'], 75000);

// Get total pipeline value
$pipelineValue = $salesManager->getTotalPipelineValue();
echo "Total pipeline value: $" . number_format($pipelineValue, 2);
```

These examples demonstrate the flexibility and power of the Monday.com PHP SDK for building real-world applications. The SDK provides a clean, type-safe interface for interacting with Monday.com's GraphQL API while handling complex column types and providing robust error handling. 