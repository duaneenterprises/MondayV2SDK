# Monday.com V2 SDK for PHP

A comprehensive PHP SDK for the Monday.com API V2, built to align with the official Monday.com GraphQL schema.

## Features

- **Official Schema Alignment**: Built to match the official Monday.com GraphQL schema from [https://api.monday.com/v2/get_schema](https://api.monday.com/v2/get_schema)
- **Type Safety**: All GraphQL queries use the correct types (e.g., `ID!` instead of `Int!`)
- **Input Validation**: Comprehensive validation and sanitization for all user-provided data
- **Complex Column Types**: Full support for email, phone, location, timeline, status, and number columns
- **Pagination**: Cursor-based pagination using official `items_page` and `next_items_page` queries
- **Rate Limiting**: Built-in rate limiting with configurable limits and periodic cleanup
- **Error Handling**: Comprehensive error handling with detailed exception types
- **Logging**: Configurable logging for debugging and monitoring
- **Search**: Advanced search using official `items_by_multiple_column_values` query

## Installation

```bash
composer require duaneenterprises/monday-v2-sdk
```

Or include the SDK files directly in your project.

## Quick Start

```php
use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;

// Initialize the client
$client = new MondayClient('your-api-token');

// Create an item with complex column types
$item = $client->items()->create([
    'board_id' => 1234567890,
    'item_name' => 'New Task',
    'column_values' => [
        new TextColumn('text_column_id', 'Task description'),
        new StatusColumn('status_column_id', 'Working', 'blue'),
        new EmailColumn('email_column_id', 'user@example.com', 'John Doe')
    ]
]);

// Search for items by column values
$items = $client->items()->searchByColumnValues(1234567890, [
    'status_column_id' => 'Working'
]);

// Get all items with pagination
$result = $client->items()->getAll(1234567890, ['limit' => 100]);
$items = $result['items'];
$cursor = $result['cursor'];

// Get next page
if ($cursor) {
    $nextPage = $client->items()->getNextPage($cursor);
}
```

## Schema Alignment

This SDK is designed to align with the official Monday.com GraphQL schema. Key alignments include:

- **Type Safety**: All GraphQL queries use the correct types (e.g., `ID!` instead of `Int!`)
- **Official Mutations**: Uses the official `create_item`, `change_multiple_column_values`, and `delete_item` mutations
- **Official Queries**: Uses the official `items_by_multiple_column_values` query for searching
- **Pagination**: Implements cursor-based pagination using `items_page` and `next_items_page`
- **Column Values**: All column types format data according to Monday.com's expected JSON structure

## Column Types

### Email and Phone Columns

Monday.com email and phone columns require both the actual value and a display text. The SDK automatically handles this:

```php
// Email column - automatically includes both email and text fields
$email = new EmailColumn('email_column_id', 'user@example.com', 'John Doe');

// Phone column - automatically includes both phone and text fields
$phone = new PhoneColumn('phone_column_id', '+1-555-123-4567', 'John Doe');
```

### Timeline Columns

Timeline columns use the official Monday.com format with `date` and `end_date` fields:

```php
$timeline = new TimelineColumn('timeline_column_id', '2024-01-01', '2024-01-31');
```

### Location Columns

Location columns support full address information with coordinates:

```php
$location = new LocationColumn('location_column_id', [
    'address' => '123 Main St',
    'city' => 'New York',
    'state' => 'NY',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060
]);
```

## Configuration

```php
$client = new MondayClient('your-api-token', [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000,
        'retry_delay' => 60,
        'cleanup_interval' => 300,  // Cleanup every 5 minutes (default)
        'max_array_size' => 10000   // Emergency cleanup threshold (default)
    ],
    'logging' => [
        'level' => 'info',
        'enabled' => true,
        'file' => '/path/to/logs/monday.log'
    ]
]);
```

## Rate Limiting & Memory Management

The SDK includes intelligent rate limiting with automatic memory management:

### Periodic Cleanup

The RateLimiter automatically performs periodic cleanup to prevent memory leaks in long-running applications:

- **Automatic Cleanup**: Removes old request timestamps and daily records
- **Configurable Intervals**: Default cleanup every 5 minutes (configurable)
- **Emergency Cleanup**: Prevents arrays from growing beyond limits
- **Performance Monitoring**: Tracks cleanup statistics and duration
- **Test Environment Support**: Special handling for testing scenarios

```php
// Get cleanup statistics
$stats = $client->getRateLimiter()->getUsageStats();
echo "Cleanups performed: " . $stats['cleanup_stats']['total_cleanups'];
echo "Average cleanup time: " . $stats['cleanup_stats']['avg_cleanup_time_ms'] . "ms";

// Force immediate cleanup (useful for testing)
$client->getRateLimiter()->forceCleanup();
```

### Rate Limiting

The SDK enforces both per-minute and daily rate limits:

```php
// Check current usage
$stats = $client->getRateLimiter()->getUsageStats();
echo "Minute requests: {$stats['minute_requests']}/{$stats['minute_limit']}";
echo "Daily requests: {$stats['daily_requests']}/{$stats['daily_limit']}";
echo "Minute remaining: {$stats['minute_remaining']}";
echo "Daily remaining: {$stats['daily_remaining']}";
```

## Input Validation & Security

The SDK includes comprehensive input validation and sanitization to ensure data integrity and security:

### Automatic Validation

All user-provided data is automatically validated and sanitized:

```php
// Board and item IDs are validated for positive integers
$board = $client->boards()->get(123456789); // ✓ Valid
$board = $client->boards()->get(-1);        // ✗ InvalidArgumentException

// Item names are sanitized for XSS prevention
$item = $client->items()->create([
    'board_id' => 123456789,
    'item_name' => 'Task<script>alert("xss")</script>' // Dangerous chars removed
]);

// Email addresses are validated for format and length
$email = new EmailColumn('email_01', 'user@example.com'); // ✓ Valid
$email = new EmailColumn('email_01', 'invalid-email');    // ✗ InvalidArgumentException

// Phone numbers are validated for minimum digit count
$phone = new PhoneColumn('phone_01', '+1-555-123-4567'); // ✓ Valid
$phone = new PhoneColumn('phone_01', '123456789');       // ✗ Too short

// Location coordinates are validated for valid ranges
$location = new LocationColumn('location_01', [
    'lat' => 40.7128,  // ✓ Valid latitude
    'lng' => -74.0060  // ✓ Valid longitude
]);
```

### Validation Features

- **Type Checking**: Ensures correct data types for all parameters
- **Length Limits**: Prevents oversized strings and arrays
- **Format Validation**: Validates emails, phone numbers, dates, coordinates
- **XSS Prevention**: Sanitizes dangerous characters from user input
- **Range Validation**: Ensures coordinates, limits, and IDs are within valid ranges
- **Graceful Errors**: Provides descriptive error messages for validation failures

### Supported Validations

| Data Type | Validation Rules |
|-----------|------------------|
| Board/Item IDs | Positive integers only |
| Names | 1-255 characters, XSS sanitized |
| Emails | Valid format, max 254 characters |
| Phone Numbers | Min 10 digits, max 20 characters |
| Locations | Valid coordinates (-90 to 90 lat, -180 to 180 lng) |
| Status Colors | Predefined color values only |
| Dates | YYYY-MM-DD format only |
| Cursors | Base64 encoded strings |
| Limits | 1-1000 range |

## Error Handling

```php
try {
    $item = $client->items()->create([
        'board_id' => 1234567890,
        'item_name' => 'Test Item'
    ]);
} catch (MondayApiException $e) {
    if ($e->isGraphQLError()) {
        $errors = $e->getGraphQLErrors();
        // Handle GraphQL errors
    }
    echo "Error: " . $e->getErrorDetails();
} catch (RateLimitException $e) {
    $retryAfter = $e->getRetryAfter();
    echo "Rate limit exceeded. Retry after {$retryAfter} seconds.";
}
```

## Documentation

- [Quick Start Guide](docs/QUICK_START.md) - Get up and running in minutes
- [API Basics](docs/API_BASICS.md) - Monday.com API fundamentals and concepts
- [API Reference](docs/API_REFERENCE.md) - Complete API documentation
- [Examples](docs/EXAMPLES.md) - Comprehensive usage examples and patterns
- [Column Types](docs/COLUMN_TYPES.md) - Detailed column type documentation
- [API Alignment](docs/API_ALIGNMENT.md) - How the SDK aligns with official Monday.com API

## Testing

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suite
php vendor/bin/phpunit --testsuite ColumnTypes
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This SDK is licensed under the MIT License. See the LICENSE file for details.

## Future Goals

We have an exciting roadmap planned to enhance the Monday.com PHP SDK. Here are our future goals organized by priority:

### 🚀 **High Priority (Next Release)**

#### **1. Enhanced Error Handling & Recovery**
- **Retry Mechanisms**: Automatic retry with exponential backoff for transient failures
- **Circuit Breaker Pattern**: Prevent cascading failures during API outages
- **Error Classification**: Categorize errors (network, authentication, rate limit, etc.)
- **Error Recovery Strategies**: Different recovery approaches based on error type

#### **2. Advanced Configuration Management**
- **Environment-based Configuration**: Support for `.env` files and environment variables
- **Configuration Validation**: Runtime validation of configuration values
- **Configuration Caching**: Cache validated configurations for performance
- **Configuration Inheritance**: Support for base configurations with overrides

#### **3. Performance Optimizations**
- **Connection Pooling**: Reuse HTTP connections for better performance
- **Request Batching**: Batch multiple API calls into single requests
- **Response Caching**: Cache frequently accessed data with TTL
- **Async/Await Support**: Non-blocking API calls for high-throughput applications

#### **4. Webhook Support**
- **Built-in Webhook Handling**: Validate and process Monday.com webhooks
- **Event-driven Architecture**: Event listeners for Monday.com changes
- **Webhook Security**: Verify webhook signatures and authenticity
- **Webhook Management**: Tools for managing webhook subscriptions

### 🔧 **Medium Priority (Future Releases)**

#### **5. Advanced Monitoring & Observability**
- **Request/Response Logging**: Detailed logging with correlation IDs
- **Performance Metrics**: Track response times, success rates, error rates
- **Health Checks**: Built-in health check endpoints
- **Distributed Tracing**: Integration with tracing systems (Jaeger, Zipkin)

#### **6. Enhanced Security Features**
- **Token Rotation**: Automatic API token refresh
- **Request Signing**: Digital signatures for enhanced security
- **Rate Limit Awareness**: Proactive rate limit management
- **Input Sanitization**: Enhanced XSS and injection protection

#### **7. Developer Experience Improvements**
- **IDE Integration**: Better autocomplete and type hints
- **Debugging Tools**: Built-in debugging utilities
- **Code Generation**: Generate code from Monday.com schemas
- **Migration Tools**: Help migrate from other Monday.com libraries

#### **8. Advanced Column Type Support**
- **Custom Column Types**: Framework for creating custom column types
- **Column Type Validation**: Runtime validation of column data
- **Column Type Conversion**: Automatic data type conversion
- **Bulk Column Operations**: Efficient bulk updates for multiple columns

#### **9. Query Builder & Advanced Queries**
- **Fluent Query Builder**: Fluent interface for building GraphQL queries
- **Query Optimization**: Automatic query optimization
- **Query Caching**: Intelligent query result caching
- **Complex Filtering**: Advanced filtering and search capabilities

#### **10. Framework Integrations**
- **Laravel Integration**: Laravel service provider and facades
- **Symfony Integration**: Symfony bundle and services
- **WordPress Plugin**: WordPress integration for CMS users
- **CI/CD Integration**: GitHub Actions, GitLab CI templates

### 🌟 **Long-term Vision**

#### **11. Real-time Features**
- **WebSocket Support**: Real-time updates from Monday.com
- **Event Streaming**: Stream Monday.com events in real-time
- **Live Collaboration**: Real-time collaboration features
- **Push Notifications**: Push notification support

#### **12. Enterprise Features**
- **Multi-tenant Support**: Support for multiple Monday.com accounts
- **Audit Logging**: Comprehensive audit trails
- **Role-based Access**: Fine-grained access control
- **Compliance Features**: GDPR, SOC2 compliance tools

#### **13. Data Management & Analytics**
- **Data Export/Import**: Bulk data operations
- **Data Validation**: Comprehensive data validation rules
- **Analytics Integration**: Built-in analytics and reporting
- **Data Migration**: Tools for migrating data between boards

#### **14. Workflow & Automation**
- **Workflow Templates**: Pre-built workflow patterns
- **Automation Rules**: Rule-based automation engine
- **Scheduled Tasks**: Automated task scheduling
- **Integration Hub**: Connect with other services and APIs

#### **15. Advanced Testing & Quality Assurance**
- **Integration Test Framework**: Comprehensive testing utilities
- **Mock Monday.com Server**: Local development server
- **Test Data Management**: Tools for managing test data
- **Performance Testing**: Load testing and benchmarking tools

### 🤝 **Community & Documentation**

#### **16. Enhanced Documentation**
- **Interactive Documentation**: API documentation with examples
- **Video Tutorials**: Step-by-step video guides
- **Best Practices Guide**: Comprehensive best practices documentation
- **Community Examples**: User-contributed examples and patterns

#### **17. Platform Integrations**
- **Cloud Platform Support**: AWS, Azure, GCP integrations
- **Monitoring Integration**: New Relic, DataDog, etc.
- **Logging Integration**: ELK stack, Splunk, etc.
- **CI/CD Templates**: Ready-to-use CI/CD configurations

---

**Have a feature request?** We'd love to hear from you! Open an issue on GitHub or contribute to the project.

## Support

For support and questions:
- Check the [documentation](docs/)
- Review the [examples](docs/EXAMPLES.md)
- Open an issue on GitHub

## Unofficial Monday.com SDK that uses the official Monday.com API

This SDK is built to work with the official Monday.com API V2. For more information about the API:

- [Monday.com API Documentation](https://developer.monday.com/api-reference/)
- [API Basics](https://developer.monday.com/api-reference/docs/basics) - Official API fundamentals
- [GraphQL Schema](https://api.monday.com/v2/get_schema)
- [API Versioning](https://developer.monday.com/api-reference/docs/versioning)
- [Rate Limits](https://developer.monday.com/api-reference/docs/rate-limits)
- [Error Handling](https://developer.monday.com/api-reference/docs/error-handling)