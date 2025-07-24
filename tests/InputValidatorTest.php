<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\Core\InputValidator;

class InputValidatorTest extends TestCase
{
    public function testValidateBoardId(): void
    {
        // Valid integer
        $this->assertEquals(123, InputValidator::validateBoardId(123));

        // Valid string
        $this->assertEquals(456, InputValidator::validateBoardId('456'));

        // Valid string with whitespace
        $this->assertEquals(789, InputValidator::validateBoardId(' 789 '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardId(0);
    }

    public function testValidateBoardIdWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardId(-1);
    }

    public function testValidateBoardIdWithInvalidString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardId('abc');
    }

    public function testValidateItemId(): void
    {
        // Valid integer
        $this->assertEquals(123, InputValidator::validateItemId(123));

        // Valid string
        $this->assertEquals(456, InputValidator::validateItemId('456'));

        // Valid string with whitespace
        $this->assertEquals(789, InputValidator::validateItemId(' 789 '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateItemId(0);
    }

    public function testValidateItemIdWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateItemId(-1);
    }

    public function testValidateItemName(): void
    {
        // Valid name
        $this->assertEquals('Test Item', InputValidator::validateItemName('Test Item'));

        // Valid name with whitespace
        $this->assertEquals('Test Item', InputValidator::validateItemName('  Test Item  '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateItemName('');
    }

    public function testValidateItemNameWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateItemName(123);
    }

    public function testValidateItemNameWithDangerousCharacters(): void
    {
        $result = InputValidator::validateItemName('Test<script>alert("xss")</script>');
        $this->assertEquals('Testscriptalert(xss)/script', $result);
    }

    public function testValidateItemNameTooLong(): void
    {
        $longName = str_repeat('a', 256);
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateItemName($longName);
    }

    public function testValidateBoardName(): void
    {
        // Valid name
        $this->assertEquals('Test Board', InputValidator::validateBoardName('Test Board'));

        // Valid name with whitespace
        $this->assertEquals('Test Board', InputValidator::validateBoardName('  Test Board  '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardName('');
    }

    public function testValidateBoardDescription(): void
    {
        // Valid description
        $this->assertEquals('Test Description', InputValidator::validateBoardDescription('Test Description'));

        // Valid description with whitespace
        $this->assertEquals('Test Description', InputValidator::validateBoardDescription('  Test Description  '));

        // Null is valid
        $this->assertNull(InputValidator::validateBoardDescription(null));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardDescription(123);
    }

    public function testValidateBoardDescriptionTooLong(): void
    {
        $longDescription = str_repeat('a', 1001);
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateBoardDescription($longDescription);
    }

    public function testValidateColumnValues(): void
    {
        // Valid column values
        $columnValues = [
            'text_column' => 'Test Value',
            'status_column' => 'Working'
        ];

        $result = InputValidator::validateColumnValues($columnValues);
        $this->assertEquals($columnValues, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateColumnValues('not an array');
    }

    public function testValidateColumnValuesWithInvalidKeys(): void
    {
        $columnValues = [
            123 => 'Test Value' // Invalid key type
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateColumnValues($columnValues);
    }

    public function testValidateColumnValuesWithInvalidKeyFormat(): void
    {
        $columnValues = [
            'invalid-key' => 'Test Value' // Invalid key format
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateColumnValues($columnValues);
    }

    public function testValidateCursor(): void
    {
        // Valid cursor
        $cursor = 'eyJib2FyZF9pZCI6MTIzNDU2Nzg5LCJpdGVtX2lkIjoxMjM0NTY3ODl9';
        $this->assertEquals($cursor, InputValidator::validateCursor($cursor));

        // Valid cursor with whitespace
        $this->assertEquals($cursor, InputValidator::validateCursor('  ' . $cursor . '  '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateCursor('');
    }

    public function testValidateCursorWithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateCursor('invalid-cursor!@#');
    }

    public function testValidateLimit(): void
    {
        // Valid limits
        $this->assertEquals(50, InputValidator::validateLimit(50));
        $this->assertEquals(100, InputValidator::validateLimit('100'));
        $this->assertEquals(500, InputValidator::validateLimit(' 500 '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLimit(0);
    }

    public function testValidateLimitTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLimit(1001);
    }

    public function testValidateOptions(): void
    {
        // Valid options
        $options = [
            'limit' => 50,
            'cursor' => 'test',
            'include_subscribers' => true
        ];

        $result = InputValidator::validateOptions($options);
        $this->assertEquals($options, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateOptions('not an array');
    }

    public function testValidateOptionsWithInvalidKeys(): void
    {
        $options = [
            123 => 'value' // Invalid key type
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateOptions($options);
    }

    public function testValidateEmail(): void
    {
        // Valid emails
        $this->assertEquals('test@example.com', InputValidator::validateEmail('test@example.com'));
        $this->assertEquals('user.name@domain.co.uk', InputValidator::validateEmail('  user.name@domain.co.uk  '));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateEmail('');
    }

    public function testValidateEmailWithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateEmail('invalid-email');
    }

    public function testValidateEmailTooLong(): void
    {
        $longEmail = str_repeat('a', 250) . '@example.com';
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateEmail($longEmail);
    }

    public function testValidatePhone(): void
    {
        // Valid phone numbers
        $this->assertEquals('+1-555-123-4567', InputValidator::validatePhone('+1-555-123-4567'));
        $this->assertEquals('5551234567', InputValidator::validatePhone('  5551234567  '));
        $this->assertEquals('(555) 123-4567', InputValidator::validatePhone('(555) 123-4567'));

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validatePhone('');
    }

    public function testValidatePhoneTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validatePhone('123456789'); // Only 9 digits
    }

    public function testValidatePhoneTooLong(): void
    {
        $longPhone = str_repeat('1', 21);
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validatePhone($longPhone);
    }

    public function testValidateLocation(): void
    {
        // Valid string location
        $location = '123 Main St, New York, NY';
        $result = InputValidator::validateLocation($location);
        $this->assertEquals(['address' => $location], $result);

        // Valid array location
        $locationArray = [
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'country_code' => 'US'
        ];

        $result = InputValidator::validateLocation($locationArray);
        $this->assertEquals($locationArray, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLocation(123);
    }

    public function testValidateLocationWithInvalidCoordinates(): void
    {
        $location = [
            'address' => 'Test',
            'lat' => 91, // Invalid latitude
            'lng' => 0
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLocation($location);
    }

    public function testValidateLocationWithInvalidCountryCode(): void
    {
        $location = [
            'address' => 'Test',
            'country_code' => 'USA' // Invalid format
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLocation($location);
    }

    public function testValidateStatus(): void
    {
        // Valid string status
        $status = 'Working';
        $result = InputValidator::validateStatus($status);
        $this->assertEquals(['labels' => [$status]], $result);

        // Valid array status
        $statusArray = [
            'labels' => ['Working', 'In Progress'],
            'color' => 'blue'
        ];

        $result = InputValidator::validateStatus($statusArray);
        $this->assertEquals($statusArray, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateStatus(123);
    }

    public function testValidateStatusWithInvalidColor(): void
    {
        $status = [
            'labels' => ['Working'],
            'color' => 'invalid-color'
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateStatus($status);
    }

    public function testValidateTimeline(): void
    {
        // Valid string timeline
        $timeline = '2024-01-01';
        $result = InputValidator::validateTimeline($timeline);
        $this->assertEquals(['date' => $timeline], $result);

        // Valid array timeline
        $timelineArray = [
            'date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ];

        $result = InputValidator::validateTimeline($timelineArray);
        $this->assertEquals($timelineArray, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateTimeline(123);
    }

    public function testValidateTimelineWithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateTimeline('01/01/2024'); // Wrong format
    }

    public function testValidateNumber(): void
    {
        // Valid numeric
        $this->assertEquals(['number' => 123.45], InputValidator::validateNumber(123.45));

        // Valid array number
        $numberArray = [
            'number' => 123.45,
            'format' => 'currency'
        ];

        $result = InputValidator::validateNumber($numberArray);
        $this->assertEquals($numberArray, $result);

        // Invalid cases
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateNumber('not a number');
    }

    public function testValidateNumberWithInvalidFormat(): void
    {
        $number = [
            'number' => 123.45,
            'format' => 'invalid-format'
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateNumber($number);
    }

    public function testValidateNumberWithInvalidValue(): void
    {
        $number = [
            'number' => 'not numeric',
            'format' => 'currency'
        ];

        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateNumber($number);
    }

    public function testValidateLocationStringTooLong(): void
    {
        $longLocation = str_repeat('a', 501);
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLocation($longLocation);
    }

    public function testValidateStatusStringTooLong(): void
    {
        $longStatus = str_repeat('a', 101);
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateStatus($longStatus);
    }

    public function testValidateTimelineEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateTimeline('');
    }

    public function testValidateLocationEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateLocation([]);
    }

    public function testValidateStatusEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateStatus([]);
    }

    public function testValidateTimelineEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateTimeline([]);
    }

    public function testValidateNumberEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        InputValidator::validateNumber([]);
    }
}
