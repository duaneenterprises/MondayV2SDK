# Monday.com PHP SDK - Column Types Documentation

This document provides detailed information about all column types supported by the Monday.com PHP SDK, including their constructors, methods, and usage examples.

## Table of Contents

1. [Overview](#overview)
2. [TextColumn](#textcolumn)
3. [NumberColumn](#numbercolumn)
4. [StatusColumn](#statuscolumn)
5. [EmailColumn](#emailcolumn)
6. [PhoneColumn](#phonecolumn)
7. [LocationColumn](#locationcolumn)
8. [TimelineColumn](#timelinecolumn)
9. [Common Methods](#common-methods)
10. [Validation Rules](#validation-rules)
11. [Best Practices](#best-practices)

## Overview

The Monday.com PHP SDK provides type-safe column classes that handle the complex data formatting required by Monday.com's GraphQL API. Each column type:

- Validates input data according to Monday.com's requirements
- Formats data in the correct JSON structure expected by the API
- Provides both raw value access and API-formatted value access
- Includes static factory methods for common use cases

## TextColumn

Represents a text column in Monday.com.

### Constructor

```php
new TextColumn(string $columnId, string $value)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$value` (string) - The text value

### Methods

#### `getValue(): string`
Returns the raw text value.

#### `getApiValue(): array`
Returns the Monday.com API-formatted value.

### Examples

```php
use MondayV2SDK\ColumnTypes\TextColumn;

// Basic text
$textColumn = new TextColumn('text_01', 'Sample text content');

// Long description
$descriptionColumn = new TextColumn('description_01', 'This is a detailed description that can span multiple lines and contain important information about the task or project.');

// Get values
echo $textColumn->getValue(); // "Sample text content"
print_r($textColumn->getApiValue()); // ['text' => 'Sample text content']
```

### API Format

```json
{
  "text": "Sample text content"
}
```

## NumberColumn

Represents a number column in Monday.com, supporting various formats like currency, percentage, and duration.

### Constructor

```php
new NumberColumn(string $columnId, float $number, string $format = null)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$number` (float) - The numeric value
- `$format` (string|null) - The number format (optional)

### Supported Formats

- `null` - Plain number
- `'currency'` - Currency format
- `'percentage'` - Percentage format
- `'duration'` - Duration format (hours)
- `'number'` - Explicit number format

### Methods

#### `getValue(): float`
Returns the raw numeric value.

#### `getApiValue(): array`
Returns the Monday.com API-formatted value.

#### `getFormat(): ?string`
Returns the number format.

### Examples

```php
use MondayV2SDK\ColumnTypes\NumberColumn;

// Plain number
$numberColumn = new NumberColumn('number_01', 42);

// Currency
$priceColumn = new NumberColumn('price_01', 99.99, 'currency');

// Percentage
$progressColumn = new NumberColumn('progress_01', 85.5, 'percentage');

// Duration (in hours)
$hoursColumn = new NumberColumn('hours_01', 8.5, 'duration');

// Get values
echo $priceColumn->getValue(); // 99.99
print_r($priceColumn->getApiValue()); // ['number' => 99.99, 'format' => 'currency']
```

### API Format

```json
{
  "number": 99.99,
  "format": "currency"
}
```

## StatusColumn

Represents a status column in Monday.com, supporting labels and colors.

### Constructor

```php
new StatusColumn(string $columnId, string $label, string $color = null)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$label` (string) - The status label
- `$color` (string|null) - The status color (optional)

### Common Colors

- `'red'` - Red status
- `'blue'` - Blue status
- `'green'` - Green status
- `'yellow'` - Yellow status
- `'orange'` - Orange status
- `'purple'` - Purple status
- `'pink'` - Pink status
- `'black'` - Black status

### Methods

#### `getValue(): array`
Returns the Monday.com API-formatted value.

#### `getLabel(): string`
Returns the status label.

#### `getColor(): ?string`
Returns the status color.

### Examples

```php
use MondayV2SDK\ColumnTypes\StatusColumn;

// Status with color
$statusColumn = new StatusColumn('status_01', 'Working', 'blue');

// Status without color
$statusColumn = new StatusColumn('status_01', 'Done');

// Common status combinations
$statuses = [
    new StatusColumn('status_01', 'Working', 'blue'),
    new StatusColumn('status_01', 'Done', 'green'),
    new StatusColumn('status_01', 'Stuck', 'red'),
    new StatusColumn('status_01', 'Review', 'yellow')
];

// Get values
echo $statusColumn->getLabel(); // "Working"
echo $statusColumn->getColor(); // "blue"
print_r($statusColumn->getValue()); // ['labels' => ['Working'], 'color' => 'blue']
```

### API Format

```json
{
  "labels": ["Working"],
  "color": "blue"
}
```

## EmailColumn

Represents an email column in Monday.com, supporting both email address and display text.

### Constructor

```php
new EmailColumn(string $columnId, string $email, string $text = null)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$email` (string) - The email address
- `$text` (string|null) - The display text (optional, defaults to email)

### Validation

- Email address must be valid according to PHP's `filter_var()` with `FILTER_VALIDATE_EMAIL`
- If no text is provided, the email address is used as the display text

### Methods

#### `getValue(): array`
Returns the Monday.com API-formatted value.

#### `getEmail(): string`
Returns the email address.

#### `getText(): string`
Returns the display text.

### Examples

```php
use MondayV2SDK\ColumnTypes\EmailColumn;

// Email with custom display text
$emailColumn = new EmailColumn('email_01', 'john.doe@example.com', 'John Doe');

// Email without custom text (uses email as display text)
$emailColumn = new EmailColumn('email_01', 'contact@example.com');

// Get values
echo $emailColumn->getEmail(); // "john.doe@example.com"
echo $emailColumn->getText(); // "John Doe"
print_r($emailColumn->getValue()); // ['email' => 'john.doe@example.com', 'text' => 'John Doe']
```

### API Format

```json
{
  "email": "john.doe@example.com",
  "text": "John Doe"
}
```

## PhoneColumn

Represents a phone column in Monday.com, supporting both phone number and display text.

### Constructor

```php
new PhoneColumn(string $columnId, string $phone, string $text = null)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$phone` (string) - The phone number
- `$text` (string|null) - The display text (optional, defaults to phone)

### Validation

- Phone number must contain at least 10 digits (after stripping non-digit characters)
- If no text is provided, the phone number is used as the display text

### Methods

#### `getValue(): array`
Returns the Monday.com API-formatted value.

#### `getPhone(): string`
Returns the phone number.

#### `getText(): string`
Returns the display text.

### Examples

```php
use MondayV2SDK\ColumnTypes\PhoneColumn;

// Phone with custom display text
$phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');

// Phone without custom text (uses phone as display text)
$phoneColumn = new PhoneColumn('phone_01', '+1-555-987-6543');

// International format
$phoneColumn = new PhoneColumn('phone_01', '+44 20 7946 0958', 'UK Office');

// Get values
echo $phoneColumn->getPhone(); // "+1-555-123-4567"
echo $phoneColumn->getText(); // "John Doe"
print_r($phoneColumn->getValue()); // ['phone' => '+1-555-123-4567', 'text' => 'John Doe']
```

### API Format

```json
{
  "phone": "+1-555-123-4567",
  "text": "John Doe"
}
```

## LocationColumn

Represents a location column in Monday.com, supporting full address information with coordinates.

### Constructor

```php
new LocationColumn(string $columnId, array|string $location)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$location` (array|string) - Location data or address string

### Location Array Format

```php
[
    'address' => '123 Main St',           // Street address
    'city' => 'New York',                 // City
    'state' => 'NY',                      // State/province
    'country' => 'USA',                   // Country
    'lat' => 40.7128,                     // Latitude (float)
    'lng' => -74.0060,                    // Longitude (float)
    'country_code' => 'US'                // Country code (optional)
]
```

### Validation

- At least one location field must be provided
- Latitude must be between -90 and 90
- Longitude must be between -180 and 180
- Country code must be a valid ISO 3166-1 alpha-2 code

### Methods

#### `getValue(): array`
Returns the Monday.com API-formatted value.

#### `getAddress(): ?string`
Returns the street address.

#### `getCity(): ?string`
Returns the city.

#### `getState(): ?string`
Returns the state/province.

#### `getCountry(): ?string`
Returns the country.

#### `getLatitude(): ?float`
Returns the latitude.

#### `getLongitude(): ?float`
Returns the longitude.

#### `getCountryCode(): ?string`
Returns the country code.

#### `getFormattedAddress(): string`
Returns a formatted address string.

### Examples

```php
use MondayV2SDK\ColumnTypes\LocationColumn;

// Full address with coordinates
$locationColumn = new LocationColumn('location_01', [
    'address' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'country' => 'USA',
    'lat' => 40.7128,
    'lng' => -74.0060,
    'country_code' => 'US'
]);

// Simple address string
$simpleLocation = new LocationColumn('location_01', '123 Main St, New York, NY 10001');

// Just city and state
$cityLocation = new LocationColumn('location_01', [
    'city' => 'San Francisco',
    'state' => 'CA',
    'country' => 'USA'
]);

// Just coordinates
$coordinateLocation = new LocationColumn('location_01', [
    'lat' => 37.7749,
    'lng' => -122.4194,
    'address' => 'San Francisco, CA'
]);

// Get values
echo $locationColumn->getFormattedAddress(); // "123 Main Street, New York, NY, USA"
echo $locationColumn->getLatitude(); // 40.7128
print_r($locationColumn->getValue()); // Full location array
```

### API Format

```json
{
  "address": "123 Main Street",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "lat": 40.7128,
  "lng": -74.0060,
  "country_code": "US"
}
```

## TimelineColumn

Represents a timeline column in Monday.com, supporting date ranges.

### Constructor

```php
new TimelineColumn(string $columnId, string $startDate, string $endDate = null)
```

**Parameters:**
- `$columnId` (string) - The column ID from Monday.com
- `$startDate` (string) - The start date (YYYY-MM-DD format)
- `$endDate` (string|null) - The end date (YYYY-MM-DD format, optional)

### Date Format

Dates must be in `YYYY-MM-DD` format (e.g., `2024-01-15`).

### Methods

#### `getValue(): array`
Returns the Monday.com API-formatted value.

#### `getStartDate(): string`
Returns the start date.

#### `getEndDate(): ?string`
Returns the end date.

#### `getDurationInDays(): int`
Returns the duration in days.

### Examples

```php
use MondayV2SDK\ColumnTypes\TimelineColumn;

// Date range
$timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');

// Single date (same start and end)
$singleDateColumn = new TimelineColumn('timeline_01', '2024-01-15');

// Project timeline
$projectTimeline = new TimelineColumn('project_timeline_01', '2024-01-01', '2024-03-31');

// Get values
echo $timelineColumn->getStartDate(); // "2024-01-01"
echo $timelineColumn->getEndDate(); // "2024-01-31"
echo $timelineColumn->getDurationInDays(); // 30
print_r($timelineColumn->getValue()); // ['date' => '2024-01-01', 'end_date' => '2024-01-31']
```

### API Format

```json
{
  "date": "2024-01-01",
  "end_date": "2024-01-31"
}
```

## Common Methods

All column types inherit from `AbstractColumnType` and provide these common methods:

### `getColumnId(): string`
Returns the column ID.

### `validate(): void`
Validates the column data according to Monday.com's requirements.

### `isEmpty(): bool`
Checks if the column value is empty.

### Static Factory Methods

Most column types provide static factory methods for common use cases:

```php
// Empty columns
$emptyText = TextColumn::empty('text_01');
$emptyEmail = EmailColumn::empty('email_01');
$emptyLocation = LocationColumn::empty('location_01');

// Common patterns
$emailWithText = EmailColumn::withText('email_01', 'user@example.com', 'John Doe');
$phoneWithText = PhoneColumn::withText('phone_01', '+1-555-123-4567', 'John Doe');
```

## Validation Rules

### TextColumn
- No specific validation rules
- Empty strings are allowed

### NumberColumn
- Value must be numeric
- Format must be one of the supported formats

### StatusColumn
- Label must be a non-empty string
- Color must be one of the supported colors

### EmailColumn
- Email must be valid according to `filter_var()` with `FILTER_VALIDATE_EMAIL`
- Text must be a non-empty string

### PhoneColumn
- Phone number must contain at least 10 digits (after stripping non-digits)
- Text must be a non-empty string

### LocationColumn
- At least one location field must be provided
- Latitude must be between -90 and 90
- Longitude must be between -180 and 180
- Country code must be a valid ISO 3166-1 alpha-2 code

### TimelineColumn
- Dates must be in YYYY-MM-DD format
- Start date must be before or equal to end date

## Best Practices

### 1. Use Type-Safe Constructors

```php
// Good
$emailColumn = new EmailColumn('email_01', 'user@example.com', 'John Doe');

// Avoid
$emailColumn = new EmailColumn('email_01', 'invalid-email');
```

### 2. Handle Validation Errors

```php
try {
    $emailColumn = new EmailColumn('email_01', 'invalid-email');
} catch (InvalidArgumentException $e) {
    // Handle validation error
    echo "Invalid email: " . $e->getMessage();
}
```

### 3. Use Appropriate Column Types

```php
// For currency values
$priceColumn = new NumberColumn('price_01', 99.99, 'currency');

// For percentages
$progressColumn = new NumberColumn('progress_01', 85.5, 'percentage');

// For durations
$hoursColumn = new NumberColumn('hours_01', 8.5, 'duration');
```

### 4. Provide Meaningful Display Text

```php
// Good - provides context
$emailColumn = new EmailColumn('email_01', 'john.doe@example.com', 'John Doe (Project Manager)');

// Avoid - uses email as display text
$emailColumn = new EmailColumn('email_01', 'john.doe@example.com');
```

### 5. Use Consistent Status Colors

```php
// Define status color mapping
$statusColors = [
    'New' => 'red',
    'Working' => 'blue',
    'Review' => 'yellow',
    'Done' => 'green',
    'Stuck' => 'orange'
];

$statusColumn = new StatusColumn('status_01', 'Working', $statusColors['Working']);
```

### 6. Handle Empty Values

```php
// Use empty factory methods for optional columns
$columnValues = [
    new TextColumn('description_01', $description),
    new StatusColumn('status_01', $status),
];

if ($email) {
    $columnValues[] = new EmailColumn('email_01', $email, $name);
} else {
    $columnValues[] = EmailColumn::empty('email_01');
}
```

### 7. Validate Data Before Creating Columns

```php
// Validate email before creating column
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailColumn = new EmailColumn('email_01', $email, $name);
} else {
    throw new InvalidArgumentException("Invalid email address: $email");
}
```

This comprehensive documentation should help you effectively use all column types in the Monday.com PHP SDK while following best practices for data validation and formatting. 