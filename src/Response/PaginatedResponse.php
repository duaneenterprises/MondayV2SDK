<?php

namespace MondayV2SDK\Response;

/**
 * Response DTO for paginated data
 * 
 * Provides type-safe access to paginated response data
 * from Monday.com API.
 */
class PaginatedResponse
{
    private ?string $cursor;
    private array $items;

    public function __construct(?string $cursor, array $items)
    {
        $this->cursor = $cursor;
        $this->items = $items;
    }

    /**
     * Get cursor for next page
     * 
     * @return string|null
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Get items
     * 
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Check if there are more pages
     * 
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->cursor !== null;
    }

    /**
     * Get item count
     * 
     * @return int
     */
    public function getCount(): int
    {
        return count($this->items);
    }

    /**
     * Check if response is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get first item
     * 
     * @return mixed|null
     */
    public function getFirstItem()
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get last item
     * 
     * @return mixed|null
     */
    public function getLastItem()
    {
        $count = count($this->items);
        return $count > 0 ? $this->items[$count - 1] : null;
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
            cursor: $data['cursor'] ?? null,
            items: $data['items'] ?? []
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
            'cursor' => $this->cursor,
            'items' => $this->items,
        ];
    }

    /**
     * Map items using a callback function
     * 
     * @param  callable $callback
     * @return array
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * Filter items using a callback function
     * 
     * @param  callable $callback
     * @return array
     */
    public function filter(callable $callback): array
    {
        return array_filter($this->items, $callback);
    }
} 