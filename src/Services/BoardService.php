<?php

namespace MondayV2SDK\Services;

use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Core\InputValidator;
use MondayV2SDK\Exceptions\MondayApiException;

/**
 * Service for managing Monday.com boards
 * 
 * Provides methods for creating, updating, deleting, and querying boards
 * and their metadata.
 */
class BoardService
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
     * Get all boards
     * 
     * @param  array<string, mixed> $options Query options
     * @return array<int, array<string, mixed>> Boards data
     * @throws MondayApiException
     */
    public function getAll(array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query {
            boards {
                id
                name
                description
                state
                created_at
                updated_at
                board_kind
                board_folder_id
                permissions
                owner {
                    id
                    name
                }
                columns {
                    id
                    title
                    type
                    settings_str
                }
            }
        }
        GRAPHQL;

        $this->logger->info('Getting all boards');

        $response = $this->httpClient->query($query);
        return $response['boards'] ?? [];
    }

    /**
     * Get a board by ID
     * 
     * @param  int $boardId Board ID
     * @return array<string, mixed> Board data
     * @throws MondayApiException
     */
    public function get(int $boardId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);

        $query = <<<'GRAPHQL'
        query ($boardId: ID!) {
            boards (ids: [$boardId]) {
                id
                name
                description
                state
                created_at
                updated_at
                board_kind
                board_folder_id
                permissions
                owner {
                    id
                    name
                }
                columns {
                    id
                    title
                    type
                    settings_str
                }
            }
        }
        GRAPHQL;

        $variables = ['boardId' => $boardId];

        $this->logger->info('Getting board', ['board_id' => $boardId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['boards'][0] ?? [];
    }

    /**
     * Create a new board
     * 
     * @param  array<string, mixed> $data Board data
     * @return array<string, mixed> Created board data
     * @throws MondayApiException
     */
    public function create(array $data): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardName = InputValidator::validateBoardName($data['name'] ?? null);
        $boardKind = $data['board_kind'] ?? 'public';
        $folderId = $data['folder_id'] ? InputValidator::validateBoardId($data['folder_id']) : null;

        $query = <<<'GRAPHQL'
        mutation ($boardName: String!, $boardKind: BoardKind!, $folderId: ID) {
            create_board (
                board_name: $boardName,
                board_kind: $boardKind,
                folder_id: $folderId
            ) {
                id
                name
                description
                state
                created_at
                updated_at
                board_kind
                board_folder_id
                permissions
                owner {
                    id
                    name
                }
            }
        }
        GRAPHQL;

        $variables = [
            'boardName' => $boardName,
            'boardKind' => $boardKind,
            'folderId' => $folderId
        ];

        $this->logger->info(
            'Creating board', [
            'board_name' => $boardName,
            'board_kind' => $boardKind
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response['create_board'] ?? [];
    }

    /**
     * Update a board
     * 
     * @param  int                  $boardId Board ID
     * @param  array<string, mixed> $data    Update data
     * @return array<string, mixed> Updated board data
     * @throws MondayApiException
     */
    public function update(int $boardId, array $data): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);
        $boardName = isset($data['name']) ? InputValidator::validateBoardName($data['name']) : null;
        $description = isset($data['description']) ? InputValidator::validateBoardDescription($data['description']) : null;

        if (!$boardName && !$description) {
            throw new \InvalidArgumentException('At least one field must be provided for update');
        }

        $query = <<<'GRAPHQL'
        mutation ($boardId: ID!, $boardName: String, $description: String) {
            update_board (
                board_id: $boardId,
                board_attribute: "name",
                new_value: $boardName
            ) @include(if: $boardName) {
                id
                name
            }
            
            update_board (
                board_id: $boardId,
                board_attribute: "description",
                new_value: $description
            ) @include(if: $description) {
                id
                description
            }
        }
        GRAPHQL;

        $variables = [
            'boardId' => $boardId,
            'boardName' => $boardName,
            'description' => $description
        ];

        $this->logger->info(
            'Updating board', [
            'board_id' => $boardId,
            'fields' => array_keys(array_filter($data))
            ]
        );

        $response = $this->httpClient->mutate($query, $variables);
        return $response;
    }

    /**
     * Delete a board
     * 
     * @param  int $boardId Board ID
     * @return array<string, mixed> Deletion result
     * @throws MondayApiException
     */
    public function delete(int $boardId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);

        $query = <<<'GRAPHQL'
        mutation ($boardId: ID!) {
            delete_board (board_id: $boardId) {
                id
            }
        }
        GRAPHQL;

        $variables = ['boardId' => $boardId];

        $this->logger->info('Deleting board', ['board_id' => $boardId]);

        $response = $this->httpClient->mutate($query, $variables);
        return $response['delete_board'] ?? [];
    }

    /**
     * Get board columns
     * 
     * @param  int $boardId Board ID
     * @return array<int, array<string, mixed>> Columns data
     * @throws MondayApiException
     */
    public function getColumns(int $boardId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);

        $query = <<<'GRAPHQL'
        query ($boardId: ID!) {
            boards (ids: [$boardId]) {
                columns {
                    id
                    title
                    type
                    settings_str
                    archived
                }
            }
        }
        GRAPHQL;

        $variables = ['boardId' => $boardId];

        $this->logger->info('Getting board columns', ['board_id' => $boardId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['boards'][0]['columns'] ?? [];
    }

    /**
     * Get board subscribers
     * 
     * @param  int $boardId Board ID
     * @return array<int, array<string, mixed>> Subscribers data
     * @throws MondayApiException
     */
    public function getSubscribers(int $boardId): array
    {
        $this->rateLimiter->checkLimit();

        // Validate and sanitize input data
        $boardId = InputValidator::validateBoardId($boardId);

        $query = <<<'GRAPHQL'
        query ($boardId: ID!) {
            boards (ids: [$boardId]) {
                subscribers {
                    id
                    name
                    email
                }
            }
        }
        GRAPHQL;

        $variables = ['boardId' => $boardId];

        $this->logger->info('Getting board subscribers', ['board_id' => $boardId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['boards'][0]['subscribers'] ?? [];
    }
} 