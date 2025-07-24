# Examples

## Project Management System

### Create a Project Board

```php
use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;

$client = new MondayClient('your-api-token');

// Create a new project board
$board = $client->boards()->create([
    'board_name' => 'Website Redesign Project',
    'board_kind' => 'public'
]);

$boardId = $board['id'];

// Create project tasks
$tasks = [
    [
        'item_name' => 'Design Homepage',
        'column_values' => [
            new TextColumn('text_01', 'Create new homepage design with modern UI'),
            new StatusColumn('status_01', 'Working', 'blue'),
            new EmailColumn('person_01', 'designer@company.com', 'Sarah Johnson'),
            new NumberColumn('numbers_01', 8, 'number'), // hours
            new TimelineColumn('date_01', '2024-02-15', '2024-02-20')
        ]
    ],
    [
        'item_name' => 'Develop Backend API',
        'column_values' => [
            new TextColumn('text_01', 'Build REST API endpoints for user management'),
            new StatusColumn('status_01', 'Not Started', 'gray'),
            new EmailColumn('person_01', 'developer@company.com', 'Mike Chen'),
            new NumberColumn('numbers_01', 16, 'number'), // hours
            new TimelineColumn('date_01', '2024-02-21', '2024-02-28')
        ]
    ],
    [
        'item_name' => 'Content Migration',
        'column_values' => [
            new TextColumn('text_01', 'Migrate existing content to new CMS'),
            new StatusColumn('status_01', 'Done', 'green'),
            new EmailColumn('person_01', 'content@company.com', 'Lisa Wang'),
            new NumberColumn('numbers_01', 12, 'number'), // hours
            new TimelineColumn('date_01', '2024-02-10', '2024-02-14')
        ]
    ]
];

foreach ($tasks as $task) {
    $task['board_id'] = $boardId;
    $client->items()->create($task);
}
```

### Track Project Progress

```php
// Get all working tasks
$workingTasks = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Working'
]);

echo "Currently working on " . count($workingTasks) . " tasks:\n";
foreach ($workingTasks as $task) {
    echo "- " . $task['name'] . "\n";
}

// Get tasks due this week
$thisWeekTasks = $client->items()->searchByColumnValues($boardId, [
    'date_01' => [
        'from' => date('Y-m-d'),
        'to' => date('Y-m-d', strtotime('+7 days'))
    ]
]);

echo "\nTasks due this week: " . count($thisWeekTasks) . "\n";
```

### Update Task Status

```php
// Find a specific task
$tasks = $client->items()->searchByColumnValues($boardId, [
    'text_01' => 'Design Homepage'
]);

if (!empty($tasks)) {
    $taskId = $tasks[0]['id'];
    
    // Mark as completed
    $client->items()->update($taskId, [
        'column_values' => [
            new StatusColumn('status_01', 'Done', 'green')
        ]
    ]);
    
    echo "Task marked as completed!\n";
}
```

## Customer Relationship Management (CRM)

### Create Customer Records

```php
use MondayV2SDK\ColumnTypes\PhoneColumn;
use MondayV2SDK\ColumnTypes\LocationColumn;

$client = new MondayClient('your-api-token');

$customers = [
    [
        'item_name' => 'Acme Corporation',
        'column_values' => [
            new TextColumn('text_01', 'Enterprise software company'),
            new StatusColumn('status_01', 'Active', 'green'),
            new EmailColumn('email_01', 'contact@acme.com', 'John Smith'),
            new PhoneColumn('phone_01', '+1-555-123-4567', 'Main Office'),
            new NumberColumn('numbers_01', 500000, 'currency'), // Annual revenue
            new LocationColumn('location_01', [
                'address' => '123 Business Ave',
                'city' => 'San Francisco',
                'country' => 'USA',
                'lat' => 37.7749,
                'lng' => -122.4194
            ])
        ]
    ],
    [
        'item_name' => 'TechStart Inc',
        'column_values' => [
            new TextColumn('text_01', 'Startup in growth phase'),
            new StatusColumn('status_01', 'Prospect', 'yellow'),
            new EmailColumn('email_01', 'ceo@techstart.com', 'Maria Garcia'),
            new PhoneColumn('phone_01', '+1-555-987-6543', 'Direct Line'),
            new NumberColumn('numbers_01', 50000, 'currency'), // Annual revenue
            new LocationColumn('location_01', [
                'address' => '456 Innovation St',
                'city' => 'Austin',
                'country' => 'USA',
                'lat' => 30.2672,
                'lng' => -97.7431
            ])
        ]
    ]
];

$boardId = 1234567890; // Your CRM board ID

foreach ($customers as $customer) {
    $customer['board_id'] = $boardId;
    $client->items()->create($customer);
}
```

### Lead Management

```php
// Get all prospects
$prospects = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Prospect'
]);

echo "Active prospects: " . count($prospects) . "\n";

// Convert prospect to customer
if (!empty($prospects)) {
    $prospectId = $prospects[0]['id'];
    
    $client->items()->update($prospectId, [
        'column_values' => [
            new StatusColumn('status_01', 'Customer', 'green')
        ]
    ]);
    
    echo "Prospect converted to customer!\n";
}

// Get high-value customers
$highValueCustomers = $client->items()->searchByColumnValues($boardId, [
    'numbers_01' => [
        'min' => 100000
    ]
]);

echo "High-value customers (>$100k): " . count($highValueCustomers) . "\n";
```

## Content Management System

### Blog Post Workflow

```php
$client = new MondayClient('your-api-token');
$boardId = 1234567890; // Content board ID

// Create blog post
$post = $client->items()->create([
    'board_id' => $boardId,
    'item_name' => '10 Tips for Better Code Quality',
    'column_values' => [
        new TextColumn('text_01', 'Technical article about coding best practices'),
        new StatusColumn('status_01', 'Draft', 'gray'),
        new EmailColumn('person_01', 'writer@company.com', 'Alex Thompson'),
        new EmailColumn('person_02', 'editor@company.com', 'Emma Wilson'),
        new TimelineColumn('date_01', '2024-02-20', '2024-02-25'),
        new NumberColumn('numbers_01', 1500, 'number') // word count
    ]
]);

// Move to review
$client->items()->update($post['id'], [
    'column_values' => [
        new StatusColumn('status_01', 'In Review', 'yellow')
    ]
]);

// Get all published posts
$publishedPosts = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Published'
]);

echo "Published posts: " . count($publishedPosts) . "\n";
```

## Event Management

### Conference Planning

```php
$client = new MondayClient('your-api-token');
$boardId = 1234567890; // Event board ID

$events = [
    [
        'item_name' => 'Annual Tech Conference 2024',
        'column_values' => [
            new TextColumn('text_01', '3-day technology conference with 500+ attendees'),
            new StatusColumn('status_01', 'Planning', 'blue'),
            new EmailColumn('person_01', 'events@company.com', 'David Brown'),
            new NumberColumn('numbers_01', 500, 'number'), // expected attendees
            new NumberColumn('numbers_02', 50000, 'currency'), // budget
            new TimelineColumn('date_01', '2024-06-15', '2024-06-17'),
            new LocationColumn('location_01', [
                'address' => 'Convention Center',
                'city' => 'Las Vegas',
                'country' => 'USA',
                'lat' => 36.1699,
                'lng' => -115.1398
            ])
        ]
    ]
];

foreach ($events as $event) {
    $event['board_id'] = $boardId;
    $client->items()->create($event);
}
```

## Inventory Management

### Product Catalog

```php
$client = new MondayClient('your-api-token');
$boardId = 1234567890; // Inventory board ID

$products = [
    [
        'item_name' => 'Wireless Headphones Pro',
        'column_values' => [
            new TextColumn('text_01', 'Premium wireless headphones with noise cancellation'),
            new StatusColumn('status_01', 'In Stock', 'green'),
            new NumberColumn('numbers_01', 299.99, 'currency'), // price
            new NumberColumn('numbers_02', 150, 'number'), // quantity
            new EmailColumn('person_01', 'supplier@electronics.com', 'Tech Supplies Inc'),
            new TimelineColumn('date_01', '2024-01-15') // restock date
        ]
    ],
    [
        'item_name' => 'Smart Watch Series 5',
        'column_values' => [
            new TextColumn('text_01', 'Advanced fitness tracking smartwatch'),
            new StatusColumn('status_01', 'Low Stock', 'yellow'),
            new NumberColumn('numbers_01', 199.99, 'currency'), // price
            new NumberColumn('numbers_02', 5, 'number'), // quantity
            new EmailColumn('person_01', 'supplier@electronics.com', 'Tech Supplies Inc'),
            new TimelineColumn('date_01', '2024-02-28') // restock date
        ]
    ]
];

foreach ($products as $product) {
    $product['board_id'] = $boardId;
    $client->items()->create($product);
}

// Check low stock items
$lowStockItems = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Low Stock'
]);

echo "Low stock items: " . count($lowStockItems) . "\n";
foreach ($lowStockItems as $item) {
    echo "- " . $item['name'] . "\n";
}
```

## Advanced Usage Patterns

### Batch Operations

```php
$client = new MondayClient('your-api-token');
$boardId = 1234567890;

// Create multiple items efficiently
$items = [];
for ($i = 1; $i <= 10; $i++) {
    $items[] = [
        'board_id' => $boardId,
        'item_name' => "Task $i",
        'column_values' => [
            new TextColumn('text_01', "Description for task $i"),
            new StatusColumn('status_01', 'Not Started', 'gray')
        ]
    ];
}

foreach ($items as $item) {
    try {
        $client->items()->create($item);
    } catch (Exception $e) {
        echo "Failed to create item: " . $e->getMessage() . "\n";
    }
}
```

### Error Handling with Retry Logic

```php
function createItemWithRetry($client, $data, $maxRetries = 3) {
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            return $client->items()->create($data);
        } catch (RateLimitException $e) {
            if ($attempt === $maxRetries) {
                throw $e;
            }
            $retryAfter = $e->getRetryAfter();
            echo "Rate limited. Waiting $retryAfter seconds...\n";
            sleep($retryAfter);
        } catch (MondayApiException $e) {
            echo "API Error: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// Usage
try {
    $item = createItemWithRetry($client, $itemData);
    echo "Item created successfully!\n";
} catch (Exception $e) {
    echo "Failed to create item after retries: " . $e->getMessage() . "\n";
}
```

### Data Export

```php
function exportBoardData($client, $boardId) {
    $allItems = [];
    $cursor = null;
    
    do {
        $options = ['limit' => 100];
        if ($cursor) {
            $options['cursor'] = $cursor;
        }
        
        $result = $client->items()->getAll($boardId, $options);
        $allItems = array_merge($allItems, $result['items']);
        $cursor = $result['cursor'];
        
    } while ($cursor);
    
    return $allItems;
}

// Export all data from a board
$boardData = exportBoardData($client, $boardId);
echo "Exported " . count($boardData) . " items from board\n";

// Convert to CSV
$csv = "Name,Status,Description\n";
foreach ($boardData as $item) {
    $name = $item['name'];
    $status = $item['column_values']['status_01']['text'] ?? '';
    $description = $item['column_values']['text_01']['text'] ?? '';
    $csv .= "\"$name\",\"$status\",\"$description\"\n";
}

file_put_contents('board_export.csv', $csv);
echo "Data exported to board_export.csv\n";
```

## Integration Examples

### Webhook Handler

```php
// Handle incoming webhooks from Monday.com
function handleMondayWebhook($payload) {
    $client = new MondayClient('your-api-token');
    
    $eventType = $payload['type'];
    $itemId = $payload['itemId'];
    
    switch ($eventType) {
        case 'create_item':
            // New item created
            $item = $client->items()->get($itemId);
            echo "New item created: " . $item['name'] . "\n";
            break;
            
        case 'update_item':
            // Item updated
            $item = $client->items()->get($itemId);
            echo "Item updated: " . $item['name'] . "\n";
            break;
            
        case 'delete_item':
            // Item deleted
            echo "Item deleted: " . $itemId . "\n";
            break;
    }
}

// Usage in webhook endpoint
$webhookData = json_decode(file_get_contents('php://input'), true);
handleMondayWebhook($webhookData);
```

### Slack Integration

```php
function notifySlack($message, $webhookUrl) {
    $data = ['text' => $message];
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    file_get_contents($webhookUrl, false, $context);
}

// Monitor for urgent tasks
$urgentTasks = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'Urgent'
]);

if (!empty($urgentTasks)) {
    $message = "🚨 " . count($urgentTasks) . " urgent tasks need attention!";
    notifySlack($message, 'your-slack-webhook-url');
}
``` 