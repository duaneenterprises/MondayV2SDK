<?php

namespace MondayV2SDK\ColumnTypes;

use MondayV2SDK\Core\InputValidator;

/**
 * Phone column type for Monday.com
 * 
 * Handles phone columns with proper validation and formatting.
 * Supports separate phone and text values, with fallback behavior.
 */
class PhoneColumn extends AbstractColumnType
{
    private string $phone;
    private ?string $text;

    /**
     * Constructor
     * 
     * @param string      $columnId       The column ID
     * @param string      $phone          The phone number
     * @param string|null $text           The display text (optional, defaults to phone)
     * @param bool        $skipValidation Whether to skip validation during construction
     */
    public function __construct(string $columnId, string $phone, ?string $text = null, bool $skipValidation = false)
    {
        // Validate and sanitize input data (skip validation if requested)
        if ($skipValidation) {
            $this->phone = $phone;
            $this->text = $text ?? $phone;
        } else {
            $this->phone = InputValidator::validatePhone($phone);
            $this->text = $text ? trim($text) : $this->phone;
        }

        parent::__construct($columnId, $this->phone, $skipValidation);
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'phone';
    }

    /**
     * Validate the phone value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if (!empty($this->phone) && !$this->isValidPhoneNumber($this->phone)) {
            throw new \InvalidArgumentException("Invalid phone number: {$this->phone}");
        }
    }

    /**
     * Get the column value for API
     * 
     * @return array<string, string>
     */
    public function getValue(): array
    {
        return [
            'phone' => $this->phone,
            'text' => $this->text ?? ''
        ];
    }

    /**
     * Create a phone column with separate phone and text values
     * 
     * @param  string $columnId The column ID
     * @param  string $phone    The phone number
     * @param  string $text     The display text
     * @return self
     */
    public static function withText(string $columnId, string $phone, string $text): self
    {
        return new self($columnId, $phone, $text);
    }

    /**
     * Create an empty phone column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '', null, true);
    }

    /**
     * Get the phone number
     * 
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Get the display text
     * 
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Validate phone number format
     * 
     * @param  string $phone
     * @return bool
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Remove all non-digit characters
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if we have at least 10 digits (standard US phone number)
        return strlen((string) $digits) >= 10;
    }


} 