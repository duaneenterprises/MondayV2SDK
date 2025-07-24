<?php

namespace MondayV2SDK\Tests\ColumnTypes;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\ColumnTypes\TextColumn;

/**
 * Unit tests for TextColumn
 */
class TextColumnTest extends TestCase
{
    private string $testColumnId = 'text_column_123';

    public function testConstructor(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $this->assertInstanceOf(TextColumn::class, $textColumn);
        $this->assertEquals($this->testColumnId, $textColumn->getColumnId());
        $this->assertEquals('Test text', $textColumn->getValue());
        $this->assertEquals('text', $textColumn->getType());
    }

    public function testValidateWithValidData(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Valid text');

        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $textColumn->validate();
    }

    public function testValidateWithEmptyColumnId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Column ID cannot be empty');

        new TextColumn('', 'Test text');
    }

    public function testValidateWithLongText(): void
    {
        $longText = str_repeat('a', 10001); // Exceeds 10000 character limit

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Text value exceeds maximum length of 10000 characters');

        new TextColumn($this->testColumnId, $longText);
    }

    public function testToJson(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $this->assertEquals('"Test text"', $textColumn->toJson());
    }

    public function testToArray(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $expected = [
            'column_id' => $this->testColumnId,
            'value' => 'Test text',
            'type' => 'text'
        ];

        $this->assertEquals($expected, $textColumn->toArray());
    }

    public function testWithMarkdown(): void
    {
        $textColumn = TextColumn::withMarkdown($this->testColumnId, '**Bold text**');

        $this->assertInstanceOf(TextColumn::class, $textColumn);
        $this->assertEquals('**Bold text**', $textColumn->getValue());
    }

    public function testEmpty(): void
    {
        $textColumn = TextColumn::empty($this->testColumnId);

        $this->assertInstanceOf(TextColumn::class, $textColumn);
        $this->assertEquals('', $textColumn->getValue());
    }

    public function testGetColumnId(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $this->assertEquals($this->testColumnId, $textColumn->getColumnId());
    }

    public function testGetValue(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $this->assertEquals('Test text', $textColumn->getValue());
    }

    public function testGetType(): void
    {
        $textColumn = new TextColumn($this->testColumnId, 'Test text');

        $this->assertEquals('text', $textColumn->getType());
    }
}
