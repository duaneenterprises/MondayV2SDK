# Monday.com PHP SDK Documentation

Welcome to the comprehensive documentation for the Monday.com PHP SDK. This SDK provides a clean, type-safe interface for interacting with Monday.com's GraphQL API.

## 📚 Documentation Index

### Getting Started
- **[Quick Start Guide](QUICK_START.md)** - Get up and running in minutes
- **[API Reference](API_REFERENCE.md)** - Complete API documentation
- **[Column Types Guide](COLUMN_TYPES.md)** - Detailed guide for all column types

### Examples and Tutorials
- **[Examples](EXAMPLES.md)** - Real-world usage examples and patterns
- **[Troubleshooting Guide](TROUBLESHOOTING.md)** - Common issues and solutions

## 🚀 Quick Start

### Installation
```bash
composer require duaneenterprises/monday-v2-sdk
```

### Basic Usage
```php
use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;

$client = new MondayClient('your-api-token');

$item = $client->items()->create([
    'board_id' => 1234567890,
    'item_name' => 'New Task',
    'column_values' => [
        new TextColumn('text_01', 'Task description'),
        new StatusColumn('status_01', 'Working', 'blue')
    ]
]);
```

## 🔧 Features

- **Type-Safe Column Types** - Dedicated classes for each Monday.com column type
- **Automatic Rate Limiting** - Built-in rate limiting with configurable limits
- **Error Handling** - Comprehensive exception handling with detailed error messages
- **Pagination Support** - Cursor-based pagination for large datasets
- **Logging** - Configurable logging for debugging and monitoring
- **GraphQL Support** - Direct GraphQL query and mutation execution

## 📋 Supported Column Types

| Column Type | Class | Description |
|-------------|-------|-------------|
| Text | `TextColumn` | Simple text input |
| Status | `StatusColumn` | Dropdown with predefined options |
| Email | `EmailColumn` | Email address with display text |
| Phone | `PhoneColumn` | Phone number with display text |
| Number | `NumberColumn` | Numeric values (currency, percentage, etc.) |
| Timeline | `TimelineColumn` | Date ranges and single dates |
| Location | `LocationColumn` | Geographic locations with coordinates |

## 🏗️ Architecture

The SDK follows SOLID principles and uses dependency injection for testability:

- **MondayClient** - Main entry point
- **Services** - Domain-specific services (ItemService, BoardService, etc.)
- **Column Types** - Type-safe column value classes
- **Core Components** - HTTP client, rate limiter, logger

## 🔍 Key Concepts

### Services
The SDK is organized into services that correspond to Monday.com entities:

- `items()` - Manage items (tasks, projects, etc.)
- `boards()` - Manage boards
- `columns()` - Manage columns
- `users()` - Manage users
- `workspaces()` - Manage workspaces

### Column Values
All column values are created using dedicated classes that ensure proper formatting:

```php
// Text column
new TextColumn('text_01', 'Description');

// Status column
new StatusColumn('status_01', 'Working', 'blue');

// Email column
new EmailColumn('email_01', 'user@example.com', 'John Doe');
```

### Error Handling
The SDK provides specific exception types for different error scenarios:

```php
try {
    $item = $client->items()->create($data);
} catch (MondayApiException $e) {
    // Handle API errors
    echo "API Error: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Handle rate limiting
    $retryAfter = $e->getRetryAfter();
    sleep($retryAfter);
}
```

## 📖 Documentation Structure

### For Beginners
1. Start with the **[Quick Start Guide](QUICK_START.md)**
2. Review **[Examples](EXAMPLES.md)** for common use cases
3. Consult **[Column Types Guide](COLUMN_TYPES.md)** for data formatting

### For Advanced Users
1. Reference the **[API Reference](API_REFERENCE.md)** for complete method documentation
2. Check **[Troubleshooting Guide](TROUBLESHOOTING.md)** for optimization tips
3. Use **[Examples](EXAMPLES.md)** for advanced patterns

### For LLMs and Developers
- All documentation is structured for easy parsing and reference
- Code examples are complete and ready to run
- Error scenarios are documented with solutions
- Best practices are clearly marked

## 🔗 Related Resources

- **[Monday.com API Documentation](https://developer.monday.com/api-reference/docs)**
- **[GraphQL Playground](https://monday.com/developers/v2/try-it-yourself)**
- **[Monday.com Status Page](https://status.monday.com)**

## 🤝 Contributing

This SDK is designed to be maintainable, testable, and observable. Contributions are welcome!

### Development Guidelines
- Follow SOLID principles
- Write comprehensive tests
- Use type hints and PHPDoc
- Follow PSR-12 coding standards

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-text

# Run static analysis
vendor/bin/phpstan analyse

# Check code style
vendor/bin/phpcs --standard=PSR12 src tests
```

## 📄 License

This SDK is licensed under the MIT License. See the LICENSE file for details.

## 🆘 Support

If you encounter issues:

1. Check the **[Troubleshooting Guide](TROUBLESHOOTING.md)**
2. Review **[Examples](EXAMPLES.md)** for similar use cases
3. Enable debug logging for detailed error information
4. Test with minimal data to isolate issues

For LLMs: This documentation is structured to provide comprehensive coverage of the SDK's capabilities, with clear examples and troubleshooting guidance. Each file serves a specific purpose and can be referenced independently based on the user's needs. 