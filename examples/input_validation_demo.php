<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\PhoneColumn;
use MondayV2SDK\ColumnTypes\LocationColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\TextColumn;

/**
 * Input Validation Demo
 * 
 * This example demonstrates the comprehensive input validation
 * and sanitization features of the Monday.com V2 SDK.
 */

echo "=== Monday.com V2 SDK - Input Validation Demo ===\n\n";

// Initialize the client (you would use your actual API token)
$client = new MondayClient('your-api-token-here', [
    'logging' => [
        'enabled' => true,
        'level' => 'info'
    ]
]);

echo "1. Board ID Validation\n";
echo "----------------------\n";

try {
    // Valid board ID
    $boardId = $client->boards()->get(123456789);
    echo "✓ Valid board ID: 123456789\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid board ID (negative)
    $boardId = $client->boards()->get(-1);
    echo "✓ Valid board ID: -1\n";
} catch (Exception $e) {
    echo "✗ Invalid board ID (-1): " . $e->getMessage() . "\n";
}

try {
    // Invalid board ID (string)
    $boardId = $client->boards()->get((int)"invalid");
    echo "✓ Valid board ID: invalid\n";
} catch (Exception $e) {
    echo "✗ Invalid board ID (invalid): " . $e->getMessage() . "\n";
}

echo "\n2. Item Name Validation\n";
echo "----------------------\n";

try {
    // Valid item name
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => 'Valid Item Name',
        'column_values' => []
    ]);
    echo "✓ Valid item name: 'Valid Item Name'\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Item name with dangerous characters (will be sanitized)
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => 'Item<script>alert("xss")</script>',
        'column_values' => []
    ]);
    echo "✓ Item name with dangerous characters (sanitized)\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Empty item name
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => '',
        'column_values' => []
    ]);
    echo "✓ Empty item name\n";
} catch (Exception $e) {
    echo "✗ Empty item name: " . $e->getMessage() . "\n";
}

echo "\n3. Email Column Validation\n";
echo "--------------------------\n";

try {
    // Valid email
    $emailColumn = new EmailColumn('email_01', 'user@example.com', 'User Name');
    echo "✓ Valid email: user@example.com\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid email format
    $emailColumn = new EmailColumn('email_01', 'invalid-email');
    echo "✓ Invalid email format\n";
} catch (Exception $e) {
    echo "✗ Invalid email format: " . $e->getMessage() . "\n";
}

try {
    // Empty email
    $emailColumn = new EmailColumn('email_01', '');
    echo "✓ Empty email\n";
} catch (Exception $e) {
    echo "✗ Empty email: " . $e->getMessage() . "\n";
}

echo "\n4. Phone Column Validation\n";
echo "--------------------------\n";

try {
    // Valid phone number
    $phoneColumn = new PhoneColumn('phone_01', '+1-555-123-4567', 'Main Office');
    echo "✓ Valid phone: +1-555-123-4567\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid phone (too short)
    $phoneColumn = new PhoneColumn('phone_01', '123456789');
    echo "✓ Invalid phone (too short)\n";
} catch (Exception $e) {
    echo "✗ Invalid phone (too short): " . $e->getMessage() . "\n";
}

echo "\n5. Location Column Validation\n";
echo "-----------------------------\n";

try {
    // Valid location string
    $locationColumn = new LocationColumn('location_01', '123 Main St, New York, NY');
    echo "✓ Valid location string\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Valid location array
    $locationColumn = new LocationColumn('location_01', [
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
        'lat' => 40.7128,
        'lng' => -74.0060,
        'country_code' => 'US'
    ]);
    echo "✓ Valid location array\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid coordinates
    $locationColumn = new LocationColumn('location_01', [
        'address' => 'Test',
        'lat' => 91, // Invalid latitude
        'lng' => 0
    ]);
    echo "✓ Invalid coordinates\n";
} catch (Exception $e) {
    echo "✗ Invalid coordinates: " . $e->getMessage() . "\n";
}

echo "\n6. Status Column Validation\n";
echo "---------------------------\n";

try {
    // Valid status string
    $statusColumn = new StatusColumn('status_01', 'Working');
    echo "✓ Valid status string\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Valid status array
    $statusColumn = new StatusColumn('status_01', 'Working', 'blue');
    echo "✓ Valid status array\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid status color
    $statusColumn = new StatusColumn('status_01', 'Working', 'invalid-color');
    echo "✓ Invalid status color\n";
} catch (Exception $e) {
    echo "✗ Invalid status color: " . $e->getMessage() . "\n";
}

echo "\n7. Timeline Column Validation\n";
echo "-----------------------------\n";

try {
    // Valid timeline string
    $timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
    echo "✓ Valid timeline string\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Valid timeline array
    $timelineColumn = new TimelineColumn('timeline_01', '2024-01-01', '2024-01-31');
    echo "✓ Valid timeline array\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid date format
    $timelineColumn = new TimelineColumn('timeline_01', '01/01/2024', '01/31/2024');
    echo "✓ Invalid date format\n";
} catch (Exception $e) {
    echo "✗ Invalid date format: " . $e->getMessage() . "\n";
}

echo "\n8. Number Column Validation\n";
echo "---------------------------\n";

try {
    // Valid number
    $numberColumn = new NumberColumn('number_01', 123.45);
    echo "✓ Valid number: 123.45\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Valid number array
    $numberColumn = new NumberColumn('number_01', 123.45, 'currency');
    echo "✓ Valid number array\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid number format
    $numberColumn = new NumberColumn('number_01', 123.45, 'invalid-format');
    echo "✓ Invalid number format\n";
} catch (Exception $e) {
    echo "✗ Invalid number format: " . $e->getMessage() . "\n";
}

echo "\n9. Pagination Validation\n";
echo "------------------------\n";

try {
    // Valid limit
    $items = $client->items()->getAll(123456789, ['limit' => 50]);
    echo "✓ Valid limit: 50\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid limit (too high)
    $items = $client->items()->getAll(123456789, ['limit' => 1001]);
    echo "✓ Invalid limit (too high)\n";
} catch (Exception $e) {
    echo "✗ Invalid limit (too high): " . $e->getMessage() . "\n";
}

try {
    // Valid cursor
    $items = $client->items()->getNextPage('eyJib2FyZF9pZCI6MTIzNDU2Nzg5LCJpdGVtX2lkIjoxMjM0NTY3ODl9');
    echo "✓ Valid cursor\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid cursor format
    $items = $client->items()->getNextPage('invalid-cursor!@#');
    echo "✓ Invalid cursor format\n";
} catch (Exception $e) {
    echo "✗ Invalid cursor format: " . $e->getMessage() . "\n";
}

echo "\n10. Column Values Validation\n";
echo "----------------------------\n";

try {
    // Valid column values (associative array)
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => 'Test Item',
        'column_values' => [
            'text_01' => 'Sample text',
            'status_01' => 'Working'
        ]
    ]);
    echo "✓ Valid column values (associative array)\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Valid column values (ColumnTypeInterface objects)
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => 'Test Item',
        'column_values' => [
            new TextColumn('text_01', 'Sample text'),
            new StatusColumn('status_01', 'Working')
        ]
    ]);
    echo "✓ Valid column values (ColumnTypeInterface objects)\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Invalid column ID format
    $item = $client->items()->create([
        'board_id' => 123456789,
        'item_name' => 'Test Item',
        'column_values' => [
            'invalid-column-id!' => 'Sample text'
        ]
    ]);
    echo "✓ Invalid column ID format\n";
} catch (Exception $e) {
    echo "✗ Invalid column ID format: " . $e->getMessage() . "\n";
}

echo "\n=== Input Validation Demo Complete ===\n";
echo "\nKey Features Demonstrated:\n";
echo "- Type checking and validation for all input parameters\n";
echo "- Sanitization of dangerous characters (XSS prevention)\n";
echo "- Length limits for strings and arrays\n";
echo "- Format validation for emails, phone numbers, dates, etc.\n";
echo "- Coordinate validation for locations\n";
echo "- Color validation for status columns\n";
echo "- Pagination parameter validation\n";
echo "- Support for both associative arrays and ColumnTypeInterface objects\n";
echo "- Graceful error handling with descriptive messages\n"; 