<?php

namespace MondayV2SDK\Tests\ColumnTypes;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\ColumnTypes\EmailColumn;

class EmailColumnTest extends TestCase
{
    public function testConstructorWithEmailOnly()
    {
        $column = new EmailColumn('email_01', 'test@example.com');

        $this->assertEquals('email', $column->getType());
        $this->assertEquals('email_01', $column->getColumnId());
        $this->assertEquals('test@example.com', $column->getEmail());
        $this->assertEquals('test@example.com', $column->getText());
    }

    public function testConstructorWithEmailAndText()
    {
        $column = new EmailColumn('email_01', 'test@example.com', 'Test User');

        $this->assertEquals('email', $column->getType());
        $this->assertEquals('email_01', $column->getColumnId());
        $this->assertEquals('test@example.com', $column->getEmail());
        $this->assertEquals('Test User', $column->getText());
    }

    public function testWithTextStaticMethod()
    {
        $column = EmailColumn::withText('email_01', 'test@example.com', 'Test User');

        $this->assertEquals('email', $column->getType());
        $this->assertEquals('email_01', $column->getColumnId());
        $this->assertEquals('test@example.com', $column->getEmail());
        $this->assertEquals('Test User', $column->getText());
    }

    public function testEmpty()
    {
        $column = EmailColumn::empty('email_01');

        $this->assertEquals('email', $column->getType());
        $this->assertEquals('email_01', $column->getColumnId());
        $this->assertEquals('', $column->getEmail());
        $this->assertEquals('', $column->getText());
    }

    public function testGetValue()
    {
        $column = new EmailColumn('email_01', 'test@example.com', 'Test User');

        $expected = [
            'email' => 'test@example.com',
            'text' => 'Test User'
        ];

        $this->assertEquals($expected, $column->getValue());
    }

    public function testGetValueWithDefaultText()
    {
        $column = new EmailColumn('email_01', 'test@example.com');

        $expected = [
            'email' => 'test@example.com',
            'text' => 'test@example.com'
        ];

        $this->assertEquals($expected, $column->getValue());
    }

    public function testValidateWithValidEmail()
    {
        $column = new EmailColumn('email_01', 'test@example.com');

        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithInvalidEmail()
    {
        $column = new EmailColumn('email_01', 'invalid-email', null, true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address: invalid-email');

        $column->validate();
    }

    public function testValidateWithEmptyEmail()
    {
        $column = EmailColumn::empty('email_01');

        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithValidEmailAndCustomText()
    {
        $column = new EmailColumn('email_01', 'test@example.com', 'Custom Display Name');

        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithInvalidEmailAndCustomText()
    {
        $column = new EmailColumn('email_01', 'invalid-email', 'Custom Display Name', true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address: invalid-email');

        $column->validate();
    }
}
