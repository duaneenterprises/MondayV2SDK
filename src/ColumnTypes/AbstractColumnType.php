<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Abstract base class for all column types
 *
 * Provides common functionality and default implementations for column types.
 * All specific column type classes should extend this class.
 */
abstract class AbstractColumnType implements ColumnTypeInterface
{
    protected string $columnId;
    protected mixed $value;

    /**
     * Constructor
     *
     * @param string $columnId       The column ID
     * @param mixed  $value          The column value
     * @param bool   $skipValidation Whether to skip validation
     */
    public function __construct(string $columnId, mixed $value, bool $skipValidation = false)
    {
        $this->columnId = $columnId;
        $this->value = $value;

        if (!$skipValidation) {
            $this->validate();
        }
    }

    /**
     * Get the column ID
     *
     * @return string
     */
    public function getColumnId(): string
    {
        return $this->columnId;
    }

    /**
     * Get the column value
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get the column value as a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        $json = json_encode($this->getValue());
        if ($json === false) {
            throw new \RuntimeException('Failed to encode value to JSON');
        }
        return $json;
    }

    /**
     * Get the column value as an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'column_id' => $this->columnId,
            'value' => $this->getValue(),
            'type' => $this->getType(),
        ];
    }

    /**
     * Validate the column value
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->columnId)) {
            throw new \InvalidArgumentException('Column ID cannot be empty');
        }
    }

    /**
     * Get the column type identifier
     *
     * @return string
     */
    abstract public function getType(): string;
}
