<?php

namespace MondayV2SDK\ColumnTypes;

use MondayV2SDK\Core\InputValidator;

/**
 * Email column type for Monday.com
 * 
 * Handles email columns with proper validation and formatting.
 * Supports separate email and text values, with fallback behavior.
 */
class EmailColumn extends AbstractColumnType
{
    private string $email;
    private ?string $text;

    /**
     * Constructor
     * 
     * @param string      $columnId       The column ID
     * @param string      $email          The email address
     * @param string|null $text           The display text (optional, defaults to email)
     * @param bool        $skipValidation Whether to skip validation during construction
     */
    public function __construct(string $columnId, string $email, ?string $text = null, bool $skipValidation = false)
    {
        // Validate and sanitize input data (skip validation if requested)
        if ($skipValidation) {
            $this->email = $email;
            $this->text = $text ?? $email;
        } else {
            $this->email = InputValidator::validateEmail($email);
            $this->text = $text ? trim($text) : $this->email;
        }
        
        parent::__construct($columnId, $this->email, $skipValidation);
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'email';
    }

    /**
     * Validate the email value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: {$this->email}");
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
            'email' => $this->email,
            'text' => $this->text ?? ''
        ];
    }

    /**
     * Create an email column with separate email and text values
     * 
     * @param  string $columnId The column ID
     * @param  string $email    The email address
     * @param  string $text     The display text
     * @return self
     */
    public static function withText(string $columnId, string $email, string $text): self
    {
        return new self($columnId, $email, $text);
    }

    /**
     * Create an empty email column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '', null, true);
    }

    /**
     * Get the email address
     * 
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
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
} 