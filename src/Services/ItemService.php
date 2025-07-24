<?php

namespace MondayV2SDK\Services;

use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Core\InputValidator;
use MondayV2SDK\ColumnTypes\ColumnTypeInterface;
use MondayV2SDK\Exceptions\MondayApiException;

/**
 * Service for managing items on Monday.com boards
 *
 * Provides methods for creating, updating, deleting, and querying items
 * with support for pagination and complex column types.
 */
class ItemService
{
    private HttpClientInterface $httpClient;
    private RateLimiter $rateLimiter;
    private Logger $logger;

    /**
     * Constructor
     *
     * @param HttpClientInterface  $httpClient
     * @param RateLimiter $rateLimiter
     * @param Logger      $logger
     */
    public function __construct(HttpClientInterface $httpClient, RateLimiter $rateLimiter, Logger $logger)
    {
        $this->httpClient = $httpClient;
        $this->rateLimiter = $rateLimiter;
        $this->logger = $logger;
    }

    /**
     * Create a new item
     *
     * @param  array<string, mixed> $data Item data
     * @return array<string, mixed> Created item data
     * @throws MondayApiException
     */
    public function create(array $data): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($data['board_id'] ?? null);
        $itemName = InputValidator::validateItemName($data['item_name'] ?? null);
        $columnValues = InputValidator::validateColumnValues($data['column_values'] ?? []);

        $formattedColumnValues = $this->formatColumnValues($columnValues);

        $query = <<<'GRAPHQL'
        mutation ($boardId: ID!, $itemName: String!, $columnValues: JSON!) {
            create_item (
                board_id: $boardId,
                item_name: $itemName,
                column_values: $columnValues
            ) {
                id
                name
                state
                created_at
                updated_at
                column_values {
                    id
                    value
                    text
                    type
                }
            }
        }
        GRAPHQL;

        $variables = [
            'boardId' => $boardId,
            'itemName' => $itemName,
            'columnValues' => json_encode($formattedColumnValues)
        ];

        $this->logger->info(
            'Creating item',
            [
            'board_id' => $boardId,
            'item_name' => $itemName,
            'column_values_count' => count($formattedColumnValues)
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response['create_item'] ?? [];
    }

    /**
     * Update an existing item
     *
     * @param  int                  $itemId Item ID
     * @param  array<string, mixed> $data   Update data
     * @return array<string, mixed> Updated item data
     * @throws MondayApiException
     */
    public function update(int $itemId, array $data): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $itemId = InputValidator::validateItemId($itemId);
        $columnValues = InputValidator::validateColumnValues($data['column_values'] ?? []);
        $formattedColumnValues = $this->formatColumnValues($columnValues);

        $query = <<<'GRAPHQL'
        mutation ($itemId: ID!, $columnValues: JSON!) {
            change_multiple_column_values (
                item_id: $itemId,
                column_values: $columnValues
            ) {
                id
                name
                state
                updated_at
                column_values {
                    id
                    value
                    text
                    type
                }
            }
        }
        GRAPHQL;

        $variables = [
            'itemId' => $itemId,
            'columnValues' => json_encode($formattedColumnValues)
        ];

        $this->logger->info(
            'Updating item',
            [
            'item_id' => $itemId,
            'column_values_count' => count($formattedColumnValues)
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response['change_multiple_column_values'] ?? [];
    }

    /**
     * Delete an item
     *
     * @param  int $itemId Item ID
     * @return array<string, mixed> Deletion result
     * @throws MondayApiException
     */
    public function delete(int $itemId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $itemId = InputValidator::validateItemId($itemId);

        $query = <<<'GRAPHQL'
        mutation ($itemId: ID!) {
            delete_item (item_id: $itemId) {
                id
            }
        }
        GRAPHQL;

        $variables = ['itemId' => $itemId];

        $this->logger->info('Deleting item', ['item_id' => $itemId]);

        $response = $this->httpClient->mutate($query, $variables);
        return $response['delete_item'] ?? [];
    }

    /**
     * Get all items from a board
     *
     * @param  int                  $boardId Board ID
     * @param  array<string, mixed> $options Query options
     * @return array<string, mixed> Items data
     * @throws MondayApiException
     */
    public function getAll(int $boardId, array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);
        $options = InputValidator::validateOptions($options);

        $limit = InputValidator::validateLimit($options['limit'] ?? 500);
        $cursor = isset($options['cursor']) && $options['cursor'] ? InputValidator::validateCursor($options['cursor']) : null;

        if ($cursor) {
            return $this->getNextPage($cursor, $options);
        }

        $query = <<<'GRAPHQL'
        query ($boardId: ID!, $limit: Int!) {
            boards (ids: [$boardId]) {
                items_page (limit: $limit) {
                    cursor
                    items {
                        id
                        name
                        state
                        created_at
                        updated_at
                        column_values {
                            id
                            value
                            text
                            type
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $variables = [
            'boardId' => $boardId,
            'limit' => $limit
        ];

        $this->logger->info(
            'Getting items from board',
            [
            'board_id' => $boardId,
            'limit' => $limit
            ]
        );

        $response = $this->httpClient->query($query, $variables);
        $itemsPage = $response['boards'][0]['items_page'] ?? [];

        return [
            'cursor' => $itemsPage['cursor'] ?? null,
            'items' => $itemsPage['items'] ?? []
        ];
    }

    /**
     * Get next page of items
     *
     * @param  string               $cursor  Cursor from previous page
     * @param  array<string, mixed> $options Query options
     * @return array<string, mixed> Items data
     * @throws MondayApiException
     */
    public function getNextPage(string $cursor, array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $cursor = InputValidator::validateCursor($cursor);
        $options = InputValidator::validateOptions($options);

        $query = <<<'GRAPHQL'
        query ($cursor: String!) {
            next_items_page (cursor: $cursor) {
                cursor
                items {
                    id
                    name
                    state
                    created_at
                    updated_at
                    column_values {
                        id
                        value
                        text
                        type
                    }
                }
            }
        }
        GRAPHQL;

        $variables = ['cursor' => $cursor];

        $this->logger->info('Getting next page of items', ['cursor' => $cursor]);

        $response = $this->httpClient->query($query, $variables);
        $itemsPage = $response['next_items_page'] ?? [];

        return [
            'cursor' => $itemsPage['cursor'] ?? null,
            'items' => $itemsPage['items'] ?? []
        ];
    }

    /**
     * Get a specific item
     *
     * @param  int $itemId Item ID
     * @return array<string, mixed> Item data
     * @throws MondayApiException
     */
    public function get(int $itemId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $itemId = InputValidator::validateItemId($itemId);

        $query = <<<'GRAPHQL'
        query ($itemId: ID!) {
            items (ids: [$itemId]) {
                id
                name
                state
                created_at
                updated_at
                column_values {
                    id
                    value
                    text
                    type
                }
            }
        }
        GRAPHQL;

        $variables = ['itemId' => $itemId];

        $this->logger->info('Getting item', ['item_id' => $itemId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['items'][0] ?? [];
    }

    /**
     * Search items by column values
     *
     * @param  int                  $boardId      Board ID
     * @param  array<string, mixed> $columnValues Column values to search for
     * @param  array<string, mixed> $options      Query options
     * @return array<int, array<string, mixed>> Matching items
     * @throws MondayApiException
     */
    public function searchByColumnValues(int $boardId, array $columnValues, array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);
        $columnValues = InputValidator::validateColumnValues($columnValues);
        $options = InputValidator::validateOptions($options);

        if (empty($columnValues)) {
            throw new \InvalidArgumentException('At least one column value must be provided');
        }

        // Use the first column value for the search
        $firstColumnId = array_key_first($columnValues);
        $firstColumnValue = $columnValues[$firstColumnId];

        $query = <<<'GRAPHQL'
        query ($boardId: ID!, $columnId: String!, $columnValue: String!) {
            items_by_multiple_column_values(
                board_id: $boardId,
                column_id: $columnId,
                column_value: $columnValue
            ) {
                id
                name
                state
                created_at
                updated_at
                column_values {
                    id
                    value
                    text
                    type
                }
            }
        }
        GRAPHQL;

        $variables = [
            'boardId' => $boardId,
            'columnId' => $firstColumnId,
            'columnValue' => $firstColumnValue
        ];

        $this->logger->info(
            'Searching items by column values',
            [
            'board_id' => $boardId,
            'column_id' => $firstColumnId,
            'column_value' => $firstColumnValue
            ]
        );

        $response = $this->httpClient->query($query, $variables);
        $items = $response['items_by_multiple_column_values'] ?? [];

        // Filter by remaining column values
        if (count($columnValues) > 1) {
            $items = $this->filterItemsByColumnValues($items, $columnValues);
        }

        return $items;
    }

    /**
     * Format column values for API
     *
     * @param  array<int, ColumnTypeInterface|array<string, mixed>> $columnValues Array of column values
     * @return array<string, string> Formatted column values
     */
    private function formatColumnValues(array $columnValues): array
    {
        $formatted = [];

        foreach ($columnValues as $columnValue) {
            if ($columnValue instanceof ColumnTypeInterface) {
                // Use getApiValue() if available, otherwise fall back to getValue()
                if (method_exists($columnValue, 'getApiValue')) {
                    $formatted[$columnValue->getColumnId()] = json_encode($columnValue->getApiValue());
                } else {
                    $formatted[$columnValue->getColumnId()] = json_encode($columnValue->getValue());
                }
            } elseif (is_array($columnValue) && isset($columnValue['column_id'])) {
                $formatted[$columnValue['column_id']] = $columnValue['value'];
            } else {
                $this->logger->warning('Invalid column value format', ['column_value' => $columnValue]);
            }
        }

        return $formatted;
    }

    /**
     * Filter items by column values
     *
     * @param  array<int, array<string, mixed>> $items        Items to filter
     * @param  array<string, mixed>             $columnValues Column values to match
     * @return array<int, array<string, mixed>> Filtered items
     */
    private function filterItemsByColumnValues(array $items, array $columnValues): array
    {
        return array_filter(
            $items,
            function ($item) use ($columnValues) {
                if (!isset($item['column_values'])) {
                    return false;
                }

                $itemColumnValues = [];
                foreach ($item['column_values'] as $columnValue) {
                    $itemColumnValues[$columnValue['id']] = $columnValue['value'];
                }

                foreach ($columnValues as $columnId => $expectedValue) {
                    if (
                        !isset($itemColumnValues[$columnId])
                        || $itemColumnValues[$columnId] != $expectedValue
                    ) {
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
