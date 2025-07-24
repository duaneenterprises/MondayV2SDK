<?php

namespace MondayV2SDK\Response;

use DateTimeImmutable;

/**
 * Response DTO for item data
 * 
 * Provides type-safe access to item response data
 * from Monday.com API.
 */
class ItemResponse
{
    private string $id;
    private string $name;
    private string $state;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private array $columnValues;

    public function __construct(
        string $id,
        string $name,
        string $state,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        array $columnValues = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->state = $state;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->columnValues = $columnValues;
    }

    /**
     * Get item ID
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get item name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get item state
     * 
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Get creation date
     * 
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get update date
     * 
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get column values
     * 
     * @return array
     */
    public function getColumnValues(): array
    {
        return $this->columnValues;
    }

    /**
     * Create from API response array
     * 
     * @param  array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            state: $data['state'] ?? '',
            createdAt: new DateTimeImmutable($data['created_at'] ?? 'now'),
            updatedAt: new DateTimeImmutable($data['updated_at'] ?? 'now'),
            columnValues: $data['column_values'] ?? []
        );
    }

    /**
     * Convert to array for backward compatibility
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'state' => $this->state,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'column_values' => $this->columnValues,
        ];
    }

    /**
     * Get column value by ID
     * 
     * @param  string $columnId
     * @return array<string, mixed>|null
     */
    public function getColumnValue(string $columnId): ?array
    {
        foreach ($this->columnValues as $columnValue) {
            if (isset($columnValue['id']) && $columnValue['id'] === $columnId) {
                return $columnValue;
            }
        }
        
        return null;
    }

    /**
     * Check if item has a specific column value
     * 
     * @param  string $columnId
     * @return bool
     */
    public function hasColumnValue(string $columnId): bool
    {
        return $this->getColumnValue($columnId) !== null;
    }

    /**
     * Get all column values as associative array
     * 
     * @return array<string, array<string, mixed>>
     */
    public function getColumnValuesMap(): array
    {
        $map = [];
        foreach ($this->columnValues as $columnValue) {
            if (isset($columnValue['id'])) {
                $map[$columnValue['id']] = $columnValue;
            }
        }
        return $map;
    }
} 