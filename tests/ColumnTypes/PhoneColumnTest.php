<?php

namespace MondayV2SDK\Tests\ColumnTypes;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\ColumnTypes\PhoneColumn;

class PhoneColumnTest extends TestCase
{
    public function testConstructorWithPhoneOnly()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567');
        
        $this->assertEquals('phone', $column->getType());
        $this->assertEquals('phone_01', $column->getColumnId());
        $this->assertEquals('+1-555-123-4567', $column->getPhone());
        $this->assertEquals('+1-555-123-4567', $column->getText());
    }

    public function testConstructorWithPhoneAndText()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');
        
        $this->assertEquals('phone', $column->getType());
        $this->assertEquals('phone_01', $column->getColumnId());
        $this->assertEquals('+1-555-123-4567', $column->getPhone());
        $this->assertEquals('John Doe', $column->getText());
    }

    public function testWithTextStaticMethod()
    {
        $column = PhoneColumn::withText('phone_01', '+1-555-123-4567', 'John Doe');
        
        $this->assertEquals('phone', $column->getType());
        $this->assertEquals('phone_01', $column->getColumnId());
        $this->assertEquals('+1-555-123-4567', $column->getPhone());
        $this->assertEquals('John Doe', $column->getText());
    }

    public function testEmpty()
    {
        $column = PhoneColumn::empty('phone_01');
        
        $this->assertEquals('phone', $column->getType());
        $this->assertEquals('phone_01', $column->getColumnId());
        $this->assertEquals('', $column->getPhone());
        $this->assertEquals('', $column->getText());
    }

    public function testGetValue()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567', 'John Doe');
        
        $expected = [
            'phone' => '+1-555-123-4567',
            'text' => 'John Doe'
        ];
        
        $this->assertEquals($expected, $column->getValue());
    }

    public function testGetValueWithDefaultText()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567');
        
        $expected = [
            'phone' => '+1-555-123-4567',
            'text' => '+1-555-123-4567'
        ];
        
        $this->assertEquals($expected, $column->getValue());
    }

    public function testValidateWithValidPhone()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567');
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithValidPhoneDigitsOnly()
    {
        $column = new PhoneColumn('phone_01', '5551234567');
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithInvalidPhone()
    {
        $column = new PhoneColumn('phone_01', '123', null, true);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number: 123');
        
        $column->validate();
    }

    public function testValidateWithEmptyPhone()
    {
        $column = PhoneColumn::empty('phone_01');
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithValidPhoneAndCustomText()
    {
        $column = new PhoneColumn('phone_01', '+1-555-123-4567', 'Custom Display Name');
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithInvalidPhoneAndCustomText()
    {
        $column = new PhoneColumn('phone_01', '123', 'Custom Display Name', true);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number: 123');
        
        $column->validate();
    }

    public function testPhoneNumberValidation()
    {
        // Valid phone numbers
        $validPhones = [
            '+1-555-123-4567',
            '5551234567',
            '(555) 123-4567',
            '555.123.4567',
            '+44 20 7946 0958',
            '1234567890'
        ];

        foreach ($validPhones as $phone) {
            $column = new PhoneColumn('phone_01', $phone);
            // validate() now returns void, so we just check it doesn't throw an exception
            $column->validate();
        }

        // Invalid phone numbers
        $invalidPhones = [
            '123',
            'abc',
            '555-123',
            ''
        ];

        foreach ($invalidPhones as $phone) {
            $column = new PhoneColumn('phone_01', $phone, null, true);
            $this->expectException(\InvalidArgumentException::class);
            $column->validate();
        }
    }
} 