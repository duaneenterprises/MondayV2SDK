# Monday.com API Basics

This document covers the fundamental concepts of the Monday.com API and how they relate to the PHP SDK.

## About the Monday.com API

The Monday.com API is built with GraphQL and provides access to all data within your Monday.com account. It supports operations on:

- **Boards** - Work management containers
- **Items** - Individual tasks, projects, or records
- **Column Values** - Data within items (text, numbers, dates, etc.)
- **Users** - Account members and their information
- **Workspaces** - Organizational units
- **Groups** - Board sections
- **Updates** - Comments and activity
- **Files** - Attachments and documents

## Who Can Use the API?

According to [Monday.com's official documentation](https://developer.monday.com/api-reference/docs/basics), the following user types can access the API:

### ✅ **Can Access API**
- **Admins** - Full access with API tokens
- **Members** - Full access with API tokens  
- **Guests** - Access via OAuth or shortLivedToken (no API key)

### ❌ **Cannot Access API**
- **Viewers** - Read-only users
- **Deactivated/Disabled users**
- **Users with unconfirmed emails**
- **Student account users**

## Supported Monday.com Products

The API currently supports these Monday.com products:

- ✅ **Work Management** - Core project and task management
- ✅ **Dev** - Software development workflows
- ✅ **Sales CRM** - Customer relationship management
- ✅ **Service** - Customer service and support

- ❌ **Workforms** - Not currently supported

## Authentication Methods

### 1. API Token (Recommended)
```php
$client = new MondayClient('your-api-token');
```

**How to get an API token:**
1. Go to Monday.com
2. Navigate to **Admin** → **API**
3. Copy your personal API token

### 2. OAuth (For Guest Users)
```php
// OAuth implementation would be handled separately
// The SDK can work with OAuth tokens once obtained
$client = new MondayClient($oauthToken);
```

### 3. Short-lived Tokens
```php
// For temporary access (e.g., from monday apps)
$client = new MondayClient($shortLivedToken);
```

## GraphQL Overview

The Monday.com API uses GraphQL, which provides several advantages:

### **Flexible Queries**
```php
// Get only the data you need
$result = $client->query('
    query {
        boards {
            id
            name
            items {
                id
                name
                column_values {
                    id
                    value
                }
            }
        }
    }
');
```

### **Type Safety**
```php
// All queries use proper GraphQL types
$result = $client->mutate('
    mutation ($boardId: ID!, $itemName: String!) {
        create_item(board_id: $boardId, item_name: $itemName) {
            id
            name
        }
    }
', [
    'boardId' => 1234567890,  // ID! type
    'itemName' => 'New Task'  // String! type
]);
```

### **Single Endpoint**
All operations go through a single GraphQL endpoint, making it efficient and consistent.

## Rate Limits

Monday.com enforces rate limits to ensure API stability:

### **Default Limits**
- **Minute Limit**: 100 requests per minute
- **Daily Limit**: 1000 requests per day

### **SDK Rate Limiting**
```php
$client = new MondayClient('your-api-token', [
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ]
]);
```

### **Handling Rate Limits**
```php
try {
    $result = $client->items()->create($itemData);
} catch (RateLimitException $e) {
    $retryAfter = $e->getRetryAfter();
    echo "Rate limit exceeded. Retry after {$retryAfter} seconds.";
    sleep($retryAfter);
    // Retry the request
}
```

## Error Handling

The Monday.com API returns structured errors that the SDK handles:

### **GraphQL Errors**
```php
try {
    $result = $client->query('query { invalid_field }');
} catch (MondayApiException $e) {
    if ($e->isGraphQLError()) {
        $errors = $e->getGraphQLErrors();
        foreach ($errors as $error) {
            echo "GraphQL Error: " . $error['message'] . "\n";
        }
    }
}
```

### **HTTP Errors**
```php
try {
    $result = $client->items()->create($itemData);
} catch (MondayApiException $e) {
    echo "HTTP Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getCode() . "\n";
}
```

### **Network Errors**
```php
try {
    $result = $client->items()->create($itemData);
} catch (MondayApiException $e) {
    echo "Network Error: " . $e->getMessage() . "\n";
}
```

## Common Use Cases

### **1. Data Integration**
```php
// Import data from external systems
$externalData = getExternalData();
foreach ($externalData as $record) {
    $client->items()->create([
        'board_id' => $boardId,
        'item_name' => $record['name'],
        'column_values' => [
            new TextColumn('description_01', $record['description']),
            new StatusColumn('status_01', $record['status'])
        ]
    ]);
}
```

### **2. Custom Reports**
```php
// Create custom dashboards and reports
$items = $client->items()->getAll($boardId);
$reportData = analyzeItems($items);
generateReport($reportData);
```

### **3. Workflow Automation**
```php
// Automate business processes
$newItems = $client->items()->searchByColumnValues($boardId, [
    'status_01' => 'New'
]);

foreach ($newItems as $item) {
    // Automatically assign to team members
    $client->items()->update($item['id'], [
        'column_values' => [
            new EmailColumn('assignee_01', 'team@company.com', 'Team Lead')
        ]
    ]);
}
```

## Best Practices

### **1. Use Pagination for Large Datasets**
```php
$allItems = [];
$result = $client->items()->getAll($boardId, ['limit' => 100]);

do {
    $allItems = array_merge($allItems, $result['items']);
    if ($result['cursor']) {
        $result = $client->items()->getNextPage($result['cursor']);
    }
} while ($result['cursor']);
```

### **2. Handle Errors Gracefully**
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
            sleep($e->getRetryAfter());
        }
    }
}
```

### **3. Use Appropriate Column Types**
```php
// Use the correct column type for your data
$columnValues = [
    new TextColumn('description_01', 'Task description'),
    new NumberColumn('budget_01', 5000, 'currency'),
    new TimelineColumn('deadline_01', '2024-03-31'),
    new EmailColumn('assignee_01', 'user@company.com', 'John Doe')
];
```

### **4. Optimize Queries**
```php
// Only request the data you need
$result = $client->query('
    query ($boardId: ID!) {
        boards(ids: [$boardId]) {
            id
            name
            items_page(limit: 50) {
                items {
                    id
                    name
                    column_values {
                        id
                        value
                    }
                }
            }
        }
    }
', ['boardId' => $boardId]);
```

## API Versioning

The Monday.com API uses versioning to ensure stability:

- **Current Version**: v2 (recommended)
- **Schema URL**: `https://api.monday.com/v2/get_schema`
- **Endpoint**: `https://api.monday.com/v2`

The PHP SDK is built specifically for API v2 and includes all the latest features and improvements.

## Getting Help

### **Official Resources**
- [Monday.com API Documentation](https://developer.monday.com/api-reference/)
- [GraphQL Schema](https://api.monday.com/v2/get_schema)
- [API Playground](https://monday.com/developers/v2/try-it-yourself)
- [Developer Community](https://community.monday.com/)

### **SDK Resources**
- [Quick Start Guide](QUICK_START.md)
- [API Reference](API_REFERENCE.md)
- [Examples](EXAMPLES.md)
- [Column Types](COLUMN_TYPES.md)

### **Support**
- Check the [Monday.com Help Center](https://support.monday.com/)
- Join the [Developer Community](https://community.monday.com/)
- Review the [API Changelog](https://developer.monday.com/api-reference/docs/changelog)

This documentation ensures your PHP SDK is fully aligned with Monday.com's official API standards and provides developers with all the information they need to successfully integrate with Monday.com. 