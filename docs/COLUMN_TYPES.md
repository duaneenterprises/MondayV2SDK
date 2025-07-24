# Column Types Guide

This guide provides detailed information about all supported column types in the Monday.com PHP SDK.

## Overview

Monday.com supports various column types, each with specific data formats and validation rules. The SDK provides dedicated classes for each column type to ensure proper data formatting and validation.

## Text Column

**Class:** `TextColumn`  
**Monday.com Type:** `text`  
**Description:** Simple text input

### Constructor
```php
new TextColumn(string $columnId, string $value)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'text_01')
- `$value` (string): The text value

### Example
```php
use MondayV2SDK\ColumnTypes\TextColumn;

$textColumn = new TextColumn('text_01', 'This is a sample text');
```

### Use Cases
- Task descriptions
- Notes and comments
- General text information
- Long-form content

## Status Column

**Class:** `StatusColumn`  
**Monday.com Type:** `status`  
**Description:** Dropdown with predefined status options

### Constructor
```php
new StatusColumn(string $columnId, string $text, string $color)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'status_01')
- `$text` (string): The status text (must match Monday.com status labels)
- `$color` (string): The status color

### Available Colors
- `red` - Red
- `green` - Green
- `blue` - Blue
- `yellow` - Yellow
- `orange` - Orange
- `purple` - Purple
- `pink` - Pink
- `gray` - Gray

### Example
```php
use MondayV2SDK\ColumnTypes\StatusColumn;

$statusColumn = new StatusColumn('status_01', 'Working', 'blue');
$doneStatus = new StatusColumn('status_01', 'Done', 'green');
$stuckStatus = new StatusColumn('status_01', 'Stuck', 'red');
```

### Common Status Values
- `Working` - In progress
- `Done` - Completed
- `Stuck` - Blocked
- `Review` - Under review
- `Not Started` - Pending

### Use Cases
- Task status tracking
- Project phases
- Approval workflows
- Priority levels

## Email Column

**Class:** `EmailColumn`  
**Monday.com Type:** `email`  
**Description:** Email address with optional display text

### Constructor
```php
new EmailColumn(string $columnId, string $email, string $text)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'email_01')
- `$email` (string): The email address
- `$text` (string): Display text (optional, defaults to email)

### Example
```php
use MondayV2SDK\ColumnTypes\EmailColumn;

$emailColumn = new EmailColumn('email_01', 'john.doe@example.com', 'John Doe');
$simpleEmail = new EmailColumn('email_01', 'contact@company.com');
```

### Use Cases
- Assignee information
- Contact details
- Notification recipients
- Customer information

## Phone Column

**Class:** `PhoneColumn`  
**Monday.com Type:** `phone`  
**Description:** Phone number with optional display text

### Constructor
```php
new PhoneColumn(string $columnId, string $phone, string $text)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'phone_01')
- `$phone` (string): The phone number
- `$text` (string): Display text (optional, defaults to phone)

### Example
```php
use MondayV2SDK\ColumnTypes\PhoneColumn;

$phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'Main Office');
$simplePhone = new PhoneColumn('phone_01', '+1-555-987-6543');
```

### Phone Number Formatting
The SDK automatically formats phone numbers to standard format:
- US numbers: `+1XXXXXXXXXX`
- International numbers: Preserved as provided
- Invalid numbers: Returned as null

### Use Cases
- Contact information
- Support hotlines
- Emergency contacts
- Customer service numbers

## Number Column

**Class:** `NumberColumn`  
**Monday.com Type:** `numbers`  
**Description:** Numeric values with various formats

### Constructor
```php
new NumberColumn(string $columnId, float|int $value, string $format)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'numbers_01')
- `$value` (float|int): The numeric value
- `$format` (string): The number format

### Available Formats
- `number` - Plain number
- `currency` - Currency format
- `percent` - Percentage format

### Example
```php
use MondayV2SDK\ColumnTypes\NumberColumn;

$priceColumn = new NumberColumn('numbers_01', 99.99, 'currency');
$percentageColumn = new NumberColumn('numbers_02', 85.5, 'percent');
$plainNumber = new NumberColumn('numbers_03', 42, 'number');
```

### Use Cases
- Pricing information
- Progress percentages
- Quantities and counts
- Budget tracking
- Performance metrics

## Timeline Column

**Class:** `TimelineColumn`  
**Monday.com Type:** `timeline`  
**Description:** Date ranges or single dates

### Constructor
```php
new TimelineColumn(string $columnId, string $startDate, string $endDate = null)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'timeline_01')
- `$startDate` (string): Start date (YYYY-MM-DD format)
- `$endDate` (string): End date (optional, defaults to start date)

### Example
```php
use MondayV2SDK\ColumnTypes\TimelineColumn;

$dateRange = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
$singleDate = new TimelineColumn('timeline_02', '2024-02-15');
$projectTimeline = new TimelineColumn('timeline_03', '2024-03-01', '2024-06-30');
```

### Date Format
- Use `YYYY-MM-DD` format (ISO 8601)
- Examples: `2024-01-15`, `2024-12-31`

### Use Cases
- Project deadlines
- Event dates
- Due dates
- Vacation periods
- Milestone tracking

## Location Column

**Class:** `LocationColumn`  
**Monday.com Type:** `location`  
**Description:** Geographic locations with address and coordinates

### Constructor
```php
new LocationColumn(string $columnId, array $location)
```

### Parameters
- `$columnId` (string): The column ID (e.g., 'location_01')
- `$location` (array): Location data

### Location Array Structure
```php
[
    'address' => string,    // Street address
    'city' => string,       // City name
    'country' => string,    // Country name
    'lat' => float,         // Latitude (optional)
    'lng' => float          // Longitude (optional)
]
```

### Example
```php
use MondayV2SDK\ColumnTypes\LocationColumn;

$fullLocation = new LocationColumn('location_01', [
    'address' => '123 Main Street',
    'city' => 'New York',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060
]);

$simpleLocation = new LocationColumn('location_02', [
    'city' => 'San Francisco',
    'country' => 'USA'
]);
```

### Use Cases
- Office locations
- Event venues
- Customer addresses
- Service areas
- Travel destinations

## Advanced Usage Patterns

### Creating Multiple Columns

```php
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;

$columnValues = [
    new TextColumn('text_01', 'Project description'),
    new StatusColumn('status_01', 'Working', 'blue'),
    new EmailColumn('person_01', 'john@example.com', 'John Doe'),
    new NumberColumn('budget_01', 15000, 'currency'),
    new TimelineColumn('deadline_01', '2024-03-31')
];
```

### Dynamic Column Creation

```php
function createColumnByType($columnId, $type, $value, $options = []) {
    switch ($type) {
        case 'text':
            return new TextColumn($columnId, $value);
        case 'status':
            return new StatusColumn($columnId, $value, $options['color'] ?? 'gray');
        case 'email':
            return new EmailColumn($columnId, $value, $options['display'] ?? $value);
        case 'phone':
            return new PhoneColumn($columnId, $value, $options['display'] ?? $value);
        case 'number':
            return new NumberColumn($columnId, $value, $options['format'] ?? 'number');
        case 'timeline':
            return new TimelineColumn($columnId, $value, $options['end_date'] ?? null);
        case 'location':
            return new LocationColumn($columnId, $value);
        default:
            throw new InvalidArgumentException("Unknown column type: $type");
    }
}

// Usage
$columns = [
    createColumnByType('text_01', 'text', 'Sample description'),
    createColumnByType('status_01', 'status', 'Working', ['color' => 'blue']),
    createColumnByType('email_01', 'email', 'user@example.com', ['display' => 'John Doe'])
];
```

### Column Validation

```php
function validateColumnValue($columnType, $value) {
    switch ($columnType) {
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'number':
            return is_numeric($value);
        case 'timeline':
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
        case 'status':
            $validStatuses = ['Working', 'Done', 'Stuck', 'Review', 'Not Started'];
            return in_array($value, $validStatuses);
        default:
            return true;
    }
}

// Usage
if (validateColumnValue('email', 'invalid-email')) {
    $emailColumn = new EmailColumn('email_01', 'invalid-email');
} else {
    echo "Invalid email address\n";
}
```

### Column Type Mapping

```php
$columnTypeMap = [
    'text' => TextColumn::class,
    'status' => StatusColumn::class,
    'email' => EmailColumn::class,
    'phone' => PhoneColumn::class,
    'numbers' => NumberColumn::class,
    'timeline' => TimelineColumn::class,
    'location' => LocationColumn::class
];

function getColumnClass($type) {
    global $columnTypeMap;
    return $columnTypeMap[$type] ?? null;
}
```

## Best Practices

### 1. Use Descriptive Column IDs
```php
// Good
new TextColumn('project_description_01', 'Project description');

// Avoid
new TextColumn('text_01', 'Project description');
```

### 2. Validate Data Before Creating Columns
```php
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailColumn = new EmailColumn('email_01', $email, $displayName);
}
```

### 3. Handle Optional Values
```php
$columnValues = [];
if (!empty($description)) {
    $columnValues[] = new TextColumn('text_01', $description);
}
if (!empty($assignee)) {
    $columnValues[] = new EmailColumn('person_01', $assignee['email'], $assignee['name']);
}
```

### 4. Use Consistent Status Values
```php
// Define status constants
class ProjectStatus {
    const NOT_STARTED = 'Not Started';
    const WORKING = 'Working';
    const REVIEW = 'Review';
    const DONE = 'Done';
    const STUCK = 'Stuck';
}

$statusColumn = new StatusColumn('status_01', ProjectStatus::WORKING, 'blue');
```

### 5. Format Dates Consistently
```php
// Use consistent date formatting
$deadline = date('Y-m-d', strtotime('+30 days'));
$timelineColumn = new TimelineColumn('deadline_01', $deadline);
```

## Error Handling

### Common Column Errors

```php
try {
    $emailColumn = new EmailColumn('email_01', 'invalid-email');
} catch (InvalidArgumentException $e) {
    echo "Invalid email format: " . $e->getMessage() . "\n";
}

try {
    $numberColumn = new NumberColumn('numbers_01', 'not-a-number', 'currency');
} catch (TypeError $e) {
    echo "Invalid number value: " . $e->getMessage() . "\n";
}
```

### Column Validation Helper

```php
function validateColumnData($columnData) {
    $errors = [];
    
    foreach ($columnData as $columnId => $data) {
        if (empty($data['type'])) {
            $errors[] = "Missing type for column $columnId";
            continue;
        }
        
        if (empty($data['value'])) {
            $errors[] = "Missing value for column $columnId";
            continue;
        }
        
        if (!validateColumnValue($data['type'], $data['value'])) {
            $errors[] = "Invalid value for column $columnId";
        }
    }
    
    return $errors;
}
```

This comprehensive guide should help LLMs understand and work with all the column types supported by the Monday.com PHP SDK. 