<?php

namespace MondayV2SDK\Services;

use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Exceptions\MondayApiException;

/**
 * Service for managing Monday.com columns
 * 
 * Provides methods for creating, updating, and querying columns
 * on Monday.com boards.
 */
class ColumnService
{
    private HttpClientInterface $httpClient;
    private RateLimiter $rateLimiter;
    private Logger $logger;

    /**
     * Constructor
     * 
     * @param HttpClient  $httpClient
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
     * Create a new column
     * 
     * @param  int                  $boardId Board ID
     * @param  array<string, mixed> $data    Column data
     * @return array<string, mixed> Created column data
     * @throws MondayApiException
     */
    public function create(int $boardId, array $data): array
    {
        $this->rateLimiter->checkLimit();

        $title = $data['title'] ?? null;
        $columnType = $data['column_type'] ?? null;

        if (!$title || !$columnType) {
            throw new \InvalidArgumentException('Title and column_type are required');
        }

        $query = <<<'GRAPHQL'
        mutation ($boardId: ID!, $title: String!, $columnType: ColumnType!) {
            create_column (
                board_id: $boardId,
                title: $title,
                column_type: $columnType
            ) {
                id
                title
                type
                settings_str
                archived
            }
        }
        GRAPHQL;

        $variables = [
            'boardId' => $boardId,
            'title' => $title,
            'columnType' => $columnType
        ];

        $this->logger->info(
            'Creating column', [
            'board_id' => $boardId,
            'title' => $title,
            'column_type' => $columnType
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response['create_column'] ?? [];
    }

    /**
     * Update a column
     * 
     * @param  string               $columnId Column ID
     * @param  array<string, mixed> $data     Update data
     * @return array<string, mixed> Updated column data
     * @throws MondayApiException
     */
    public function update(string $columnId, array $data): array
    {
        $this->rateLimiter->checkLimit();

        $title = $data['title'] ?? null;
        $settings = $data['settings'] ?? null;

        if (!$title && !$settings) {
            throw new \InvalidArgumentException('At least one field must be provided for update');
        }

        $query = <<<'GRAPHQL'
        mutation ($columnId: String!, $title: String, $settings: String) {
            change_column_title (
                column_id: $columnId,
                title: $title
            ) @include(if: $title) {
                id
                title
            }
            
            change_column_metadata (
                column_id: $columnId,
                column_property: "settings",
                value: $settings
            ) @include(if: $settings) {
                id
                settings_str
            }
        }
        GRAPHQL;

        $variables = [
            'columnId' => $columnId,
            'title' => $title,
            'settings' => $settings ? json_encode($settings) : null
        ];

        $this->logger->info(
            'Updating column', [
            'column_id' => $columnId,
            'fields' => array_keys(array_filter($data))
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response;
    }

    /**
     * Delete a column
     * 
     * @param  string $columnId Column ID
     * @return array<string, mixed> Deletion result
     * @throws MondayApiException
     */
    public function delete(string $columnId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        mutation ($columnId: String!) {
            delete_column (column_id: $columnId) {
                id
            }
        }
        GRAPHQL;

        $variables = ['columnId' => $columnId];

        $this->logger->info('Deleting column', ['column_id' => $columnId]);

        $response = $this->httpClient->mutate($query, $variables);
        return $response['delete_column'] ?? [];
    }

    /**
     * Archive a column
     * 
     * @param  string $columnId Column ID
     * @return array<string, mixed> Archive result
     * @throws MondayApiException
     */
    public function archive(string $columnId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        mutation ($columnId: String!) {
            archive_column (column_id: $columnId) {
                id
                archived
            }
        }
        GRAPHQL;

        $variables = ['columnId' => $columnId];

        $this->logger->info('Archiving column', ['column_id' => $columnId]);

        $response = $this->httpClient->mutate($query, $variables);
        return $response['archive_column'] ?? [];
    }

    /**
     * Get column metadata
     * 
     * @param  string $columnId Column ID
     * @return array<string, mixed> Column metadata
     * @throws MondayApiException
     */
    public function getMetadata(string $columnId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($columnId: String!) {
            columns (ids: [$columnId]) {
                id
                title
                type
                settings_str
                archived
                width
            }
        }
        GRAPHQL;

        $variables = ['columnId' => $columnId];

        $this->logger->info('Getting column metadata', ['column_id' => $columnId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['columns'][0] ?? [];
    }

    /**
     * Get available column types
     * 
     * @return array<string, string> Column types
     */
    public function getAvailableTypes(): array
    {
        return [
            'text' => 'Text',
            'number' => 'Number',
            'status' => 'Status',
            'date' => 'Date',
            'timeline' => 'Timeline',
            'email' => 'Email',
            'phone' => 'Phone',
            'location' => 'Location',
            'person' => 'Person',
            'file' => 'File',
            'checkbox' => 'Checkbox',
            'dropdown' => 'Dropdown',
            'multiple_person' => 'Multiple People',
            'link' => 'Link',
            'color' => 'Color',
            'rating' => 'Rating',
            'time' => 'Time',
            'world_clock' => 'World Clock',
            'hour' => 'Hour',
            'week' => 'Week',
            'country' => 'Country',
            'sub_items' => 'Sub-items',
            'board_relation' => 'Board Relation',
            'mirror' => 'Mirror',
            'button' => 'Button',
            'auto_number' => 'Auto Number',
            'formula' => 'Formula',
            'last_updated' => 'Last Updated',
            'created_time' => 'Created Time',
            'tags' => 'Tags'
        ];
    }
} 