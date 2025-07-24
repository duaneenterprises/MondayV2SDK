<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Interface for all Monday.com column types
 * 
 * This interface defines the contract that all column type classes must implement.
 * It ensures consistent behavior across all column types and provides a common
 * way to serialize column values for the Monday.com API.
 */
interface ColumnTypeInterface
{
    /**
     * Get the column ID
     * 
     * @return string
     */
    public function getColumnId(): string;

    /**
     * Get the column value as it should be sent to Monday.com API
     * 
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string;

    /**
     * Validate the column value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void;

    /**
     * Get the column value as a JSON string (for API transmission)
     * 
     * @return string
     */
    public function toJson(): string;

    /**
     * Get the column value as an array (for internal use)
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array;
} 