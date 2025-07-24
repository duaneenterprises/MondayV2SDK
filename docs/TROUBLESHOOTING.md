# Troubleshooting Guide

This guide helps you resolve common issues when using the Monday.com PHP SDK.

## Common Errors and Solutions

### 1. Authentication Errors

#### Error: "Invalid API token"
```
MondayApiException: Invalid API token
```

**Solution:**
- Verify your API token is correct
- Check that the token hasn't expired
- Ensure the token has the necessary permissions

```php
// Verify token format
$token = 'your-api-token-here';
if (strlen($token) < 10) {
    echo "Token appears to be too short\n";
}

// Test token with a simple query
try {
    $client = new MondayClient($token);
    $me = $client->users()->getCurrent();
    echo "Token is valid. Logged in as: " . $me['name'] . "\n";
} catch (MondayApiException $e) {
    echo "Token validation failed: " . $e->getMessage() . "\n";
}
```

#### Error: "Insufficient permissions"
```
MondayApiException: Insufficient permissions to access this resource
```

**Solution:**
- Check your account permissions in Monday.com
- Ensure the token has access to the specific board/workspace
- Contact your Monday.com administrator

### 2. Column Type Errors

#### Error: "Invalid column ID"
```
MondayApiException: Column not found
```

**Solution:**
- Verify the column ID exists in your board
- Column IDs are usually in format like `text_01`, `status_01`, etc.
- Get column IDs from the board structure

```php
// Get board columns to find correct IDs
$columns = $client->columns()->getAll($boardId);
foreach ($columns as $column) {
    echo "Column: " . $column['title'] . " (ID: " . $column['id'] . ")\n";
}
```

#### Error: "Invalid status value"
```
MondayApiException: Invalid status value
```

**Solution:**
- Use exact status labels from your Monday.com board
- Common statuses: `Working`, `Done`, `Stuck`, `Review`, `Not Started`
- Check the board for available status options

```php
// Get available status options
$statusColumn = $client->columns()->get($columnId);
$statusOptions = json_decode($statusColumn['settings_str'], true);
print_r($statusOptions['labels']);
```

#### Error: "Invalid email format"
```
InvalidArgumentException: Invalid email address
```

**Solution:**
- Ensure email is in valid format
- Use `filter_var()` to validate before creating column

```php
$email = 'user@example.com';
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailColumn = new EmailColumn('email_01', $email, 'User Name');
} else {
    echo "Invalid email format\n";
}
```

### 3. Rate Limiting Issues

#### Error: "Rate limit exceeded"
```
RateLimitException: Rate limit exceeded
```

**Solution:**
- Implement retry logic with exponential backoff
- Reduce request frequency
- Increase rate limits in configuration

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
        }
    }
}
```

### 4. Data Format Errors

#### Error: "Invalid date format"
```
InvalidArgumentException: Invalid date format
```

**Solution:**
- Use YYYY-MM-DD format for dates
- Validate date format before creating timeline columns

```php
$date = '2024-01-15';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $timelineColumn = new TimelineColumn('timeline_01', $date);
} else {
    echo "Invalid date format. Use YYYY-MM-DD\n";
}
```

#### Error: "Invalid phone number"
```
InvalidArgumentException: Invalid phone number
```

**Solution:**
- Ensure phone number has at least 10 digits
- Use international format when possible

```php
$phone = '+1-555-123-4567';
$cleaned = preg_replace('/[^\d]/', '', $phone);
if (strlen($cleaned) >= 10) {
    $phoneColumn = new PhoneColumn('phone_01', $phone, 'Contact');
} else {
    echo "Phone number must have at least 10 digits\n";
}
```

### 5. Network and Connection Issues

#### Error: "Connection timeout"
```
GuzzleHttp\Exception\ConnectException: Connection timeout
```

**Solution:**
- Check internet connection
- Increase timeout in configuration
- Verify Monday.com API is accessible

```php
$client = new MondayClient('token', [
    'timeout' => 60, // Increase timeout to 60 seconds
]);
```

#### Error: "SSL certificate error"
```
GuzzleHttp\Exception\SSLException: SSL certificate verification failed
```

**Solution:**
- Update SSL certificates
- Check system time is correct
- Verify Monday.com SSL certificate

### 6. Pagination Issues

#### Error: "Invalid cursor"
```
MondayApiException: Invalid cursor
```

**Solution:**
- Don't reuse expired cursors
- Handle cursor expiration gracefully

```php
function getAllItems($client, $boardId) {
    $allItems = [];
    $cursor = null;
    
    do {
        try {
            $options = ['limit' => 100];
            if ($cursor) {
                $options['cursor'] = $cursor;
            }
            
            $result = $client->items()->getAll($boardId, $options);
            $allItems = array_merge($allItems, $result['items']);
            $cursor = $result['cursor'];
            
        } catch (MondayApiException $e) {
            if (strpos($e->getMessage(), 'Invalid cursor') !== false) {
                echo "Cursor expired, starting fresh...\n";
                $cursor = null;
                continue;
            }
            throw $e;
        }
    } while ($cursor);
    
    return $allItems;
}
```

## Debugging Techniques

### 1. Enable Logging

```php
$client = new MondayClient('token', [
    'logging' => [
        'level' => 'debug',
        'enabled' => true
    ]
]);
```

### 2. Check API Response

```php
try {
    $result = $client->items()->create($data);
} catch (MondayApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    $errors = $e->getErrors();
    print_r($errors);
}
```

### 3. Validate Data Before Sending

```php
function validateItemData($data) {
    $errors = [];
    
    if (empty($data['board_id'])) {
        $errors[] = "Board ID is required";
    }
    
    if (empty($data['item_name'])) {
        $errors[] = "Item name is required";
    }
    
    if (isset($data['column_values'])) {
        foreach ($data['column_values'] as $column) {
            if (!$column instanceof ColumnTypeInterface) {
                $errors[] = "Invalid column type";
            }
        }
    }
    
    return $errors;
}

$errors = validateItemData($itemData);
if (!empty($errors)) {
    echo "Validation errors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
```

### 4. Test with Minimal Data

```php
// Start with minimal data to isolate issues
$minimalItem = [
    'board_id' => $boardId,
    'item_name' => 'Test Item'
];

try {
    $result = $client->items()->create($minimalItem);
    echo "Basic item created successfully\n";
} catch (Exception $e) {
    echo "Basic item failed: " . $e->getMessage() . "\n";
}
```

## Performance Issues

### 1. Slow Response Times

**Solutions:**
- Use pagination with appropriate limits
- Implement caching for frequently accessed data
- Batch operations when possible

```php
// Use smaller page sizes for faster responses
$result = $client->items()->getAll($boardId, ['limit' => 25]);

// Cache board structure
$cacheFile = 'board_' . $boardId . '_cache.json';
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
    $columns = json_decode(file_get_contents($cacheFile), true);
} else {
    $columns = $client->columns()->getAll($boardId);
    file_put_contents($cacheFile, json_encode($columns));
}
```

### 2. Memory Issues

**Solutions:**
- Process large datasets in chunks
- Use generators for large result sets
- Clear variables when done

```php
function processItemsInChunks($client, $boardId, $chunkSize = 100) {
    $cursor = null;
    $processed = 0;
    
    do {
        $options = ['limit' => $chunkSize];
        if ($cursor) {
            $options['cursor'] = $cursor;
        }
        
        $result = $client->items()->getAll($boardId, $options);
        
        foreach ($result['items'] as $item) {
            // Process item
            processItem($item);
            $processed++;
        }
        
        $cursor = $result['cursor'];
        
        // Clear memory
        unset($result['items']);
        gc_collect_cycles();
        
    } while ($cursor);
    
    return $processed;
}
```

## Common Patterns

### 1. Error Recovery

```php
function robustItemCreation($client, $data, $maxRetries = 3) {
    $attempts = 0;
    
    while ($attempts < $maxRetries) {
        try {
            return $client->items()->create($data);
        } catch (RateLimitException $e) {
            $attempts++;
            if ($attempts >= $maxRetries) {
                throw $e;
            }
            sleep($e->getRetryAfter());
        } catch (MondayApiException $e) {
            // Don't retry on API errors
            throw $e;
        } catch (Exception $e) {
            $attempts++;
            if ($attempts >= $maxRetries) {
                throw $e;
            }
            sleep(1); // Wait 1 second before retry
        }
    }
}
```

### 2. Data Validation

```php
class ItemValidator {
    public static function validate($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['board_id'])) {
            $errors[] = "Board ID is required";
        }
        
        if (empty($data['item_name'])) {
            $errors[] = "Item name is required";
        }
        
        // Validate column values
        if (isset($data['column_values'])) {
            foreach ($data['column_values'] as $column) {
                if (!$column instanceof ColumnTypeInterface) {
                    $errors[] = "Invalid column type";
                }
            }
        }
        
        return $errors;
    }
}
```

### 3. Batch Processing

```php
function batchCreateItems($client, $boardId, $items, $batchSize = 10) {
    $results = [];
    $errors = [];
    
    $batches = array_chunk($items, $batchSize);
    
    foreach ($batches as $batch) {
        foreach ($batch as $item) {
            $item['board_id'] = $boardId;
            
            try {
                $result = $client->items()->create($item);
                $results[] = $result;
            } catch (Exception $e) {
                $errors[] = [
                    'item' => $item,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Small delay between batches to avoid rate limiting
        usleep(100000); // 0.1 seconds
    }
    
    return [
        'success' => $results,
        'errors' => $errors
    ];
}
```

## Getting Help

### 1. Check Documentation
- Review the [API Reference](API_REFERENCE.md)
- Check [Examples](EXAMPLES.md) for similar use cases
- Consult [Column Types Guide](COLUMN_TYPES.md)

### 2. Enable Debug Mode
```php
$client = new MondayClient('token', [
    'logging' => [
        'level' => 'debug',
        'enabled' => true
    ]
]);
```

### 3. Test with Monday.com API Directly
Use tools like GraphQL Playground or Postman to test API calls directly.

### 4. Check Monday.com Status
Verify Monday.com services are operational at [status.monday.com](https://status.monday.com).

This troubleshooting guide should help you resolve most common issues with the Monday.com PHP SDK. 