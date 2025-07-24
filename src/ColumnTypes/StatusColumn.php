<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Status column type for Monday.com
 * 
 * Handles status columns with labels and colors.
 */
class StatusColumn extends AbstractColumnType
{
    private string $label;
    private ?string $color;

    /**
     * Constructor
     * 
     * @param string      $columnId The column ID
     * @param string      $label    The status label
     * @param string|null $color    The status color (optional)
     */
    public function __construct(string $columnId, string $label, ?string $color = null)
    {
        $this->label = $label;
        $this->color = $color;
        
        parent::__construct(
            $columnId, [
            'label' => $label,
            'color' => $color
            ]
        );
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'status';
    }

    /**
     * Validate the status value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if (empty($this->label)) {
            throw new \InvalidArgumentException('Status label cannot be empty');
        }

        if ($this->color && !$this->isValidColor($this->color)) {
            throw new \InvalidArgumentException("Invalid color format: {$this->color}");
        }
    }

    /**
     * Get the column value for API
     * 
     * @return array<string, mixed>
     */
    public function getValue(): array
    {
        $value = ['labels' => [$this->label]];
        
        if ($this->color) {
            $value['color'] = $this->color;
        }
        
        return $value;
    }

    /**
     * Create a status column with just a label
     * 
     * @param  string $columnId The column ID
     * @param  string $label    The status label
     * @return self
     */
    public static function withLabel(string $columnId, string $label): self
    {
        return new self($columnId, $label);
    }

    /**
     * Create a status column with label and color
     * 
     * @param  string $columnId The column ID
     * @param  string $label    The status label
     * @param  string $color    The status color
     * @return self
     */
    public static function withLabelAndColor(string $columnId, string $label, string $color): self
    {
        return new self($columnId, $label, $color);
    }

    /**
     * Create an empty status column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '');
    }

    /**
     * Get the status label
     * 
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the status color
     * 
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Validate color format
     * 
     * @param  string $color
     * @return bool
     */
    private function isValidColor(string $color): bool
    {
        // Monday.com uses color names or hex codes
        $validColors = [
            'red', 'orange', 'yellow', 'green', 'blue', 'purple', 'pink', 'gray',
            'light_red', 'light_orange', 'light_yellow', 'light_green', 'light_blue', 'light_purple', 'light_pink', 'light_gray'
        ];

        if (in_array(strtolower($color), $validColors)) {
            return true;
        }

        // Check if it's a valid hex color
        return preg_match('/^#[0-9A-F]{6}$/i', $color) === 1;
    }
} 