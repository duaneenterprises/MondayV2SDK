<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Text column type for Monday.com
 * 
 * Handles text and long text columns. Supports both simple text values
 * and formatted text with markdown.
 */
class TextColumn extends AbstractColumnType
{
    private const MAX_LENGTH = 10000;

    /**
     * Constructor
     * 
     * @param string $columnId The column ID
     * @param string $text     The text value
     */
    public function __construct(string $columnId, string $text)
    {
        parent::__construct($columnId, $text);
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'text';
    }

    /**
     * Validate the text value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if (strlen($this->value) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                "Text value exceeds maximum length of " . self::MAX_LENGTH . " characters"
            );
        }
    }

    /**
     * Get the column value for API
     * 
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the column value in Monday.com API format
     * 
     * @return array<string, string>
     */
    public function getApiValue(): array
    {
        return ['text' => $this->value];
    }

    /**
     * Create a text column with markdown formatting
     * 
     * @param  string $columnId The column ID
     * @param  string $text     The text value
     * @return self
     */
    public static function withMarkdown(string $columnId, string $text): self
    {
        return new self($columnId, $text);
    }

    /**
     * Create an empty text column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '');
    }
} 