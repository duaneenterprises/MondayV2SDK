<?php

namespace MondayV2SDK\Services;

use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Exceptions\MondayApiException;

/**
 * Service for managing Monday.com workspaces
 *
 * Provides methods for querying workspace information and managing workspace-related data.
 */
class WorkspaceService
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
     * Get all workspaces
     *
     * @param  array<string, mixed> $options Query options
     * @return array<int, array<string, mixed>> Workspaces data
     * @throws MondayApiException
     */
    public function getAll(array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query {
            workspaces {
                id
                name
                kind
                description
                state
                created_at
                updated_at
                settings
                picture_url
                account {
                    id
                    name
                    first_day_of_the_week
                    show_timeline_weekends
                    slug
                }
                owners {
                    id
                    name
                    email
                }
                subscribers {
                    id
                    name
                    email
                }
            }
        }
        GRAPHQL;

        $this->logger->info('Getting all workspaces');

        $response = $this->httpClient->query($query);
        return $response['workspaces'] ?? [];
    }

    /**
     * Get a workspace by ID
     *
     * @param  int $workspaceId Workspace ID
     * @return array<string, mixed> Workspace data
     * @throws MondayApiException
     */
    public function get(int $workspaceId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($workspaceId: ID!) {
            workspaces (ids: [$workspaceId]) {
                id
                name
                kind
                description
                state
                created_at
                updated_at
                settings
                picture_url
                account {
                    id
                    name
                    first_day_of_the_week
                    show_timeline_weekends
                    slug
                }
                owners {
                    id
                    name
                    email
                }
                subscribers {
                    id
                    name
                    email
                }
            }
        }
        GRAPHQL;

        $variables = ['workspaceId' => $workspaceId];

        $this->logger->info('Getting workspace', ['workspace_id' => $workspaceId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['workspaces'][0] ?? [];
    }

    /**
     * Get boards in a workspace
     *
     * @param  int                  $workspaceId Workspace ID
     * @param  array<string, mixed> $options     Query options
     * @return array<int, array<string, mixed>> Boards data
     * @throws MondayApiException
     */
    public function getBoards(int $workspaceId, array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($workspaceId: ID!) {
            workspaces (ids: [$workspaceId]) {
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
                }
            }
        }
        GRAPHQL;

        $variables = ['workspaceId' => $workspaceId];

        $this->logger->info('Getting boards in workspace', ['workspace_id' => $workspaceId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['workspaces'][0]['boards'] ?? [];
    }

    /**
     * Get workspace subscribers
     *
     * @param  int $workspaceId Workspace ID
     * @return array<int, array<string, mixed>> Subscribers data
     * @throws MondayApiException
     */
    public function getSubscribers(int $workspaceId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($workspaceId: ID!) {
            workspaces (ids: [$workspaceId]) {
                subscribers {
                    id
                    name
                    email
                    created_at
                    updated_at
                    state
                    photo_thumb
                    photo_thumb_small
                    photo_original
                    url
                    title
                    birthday
                    country_code
                    location
                    time_zone_identifier
                    phone
                    mobile_phone
                    is_admin
                    is_guest
                    is_pending
                    is_verified
                    join_date
                    sign_up_date
                }
            }
        }
        GRAPHQL;

        $variables = ['workspaceId' => $workspaceId];

        $this->logger->info('Getting workspace subscribers', ['workspace_id' => $workspaceId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['workspaces'][0]['subscribers'] ?? [];
    }

    /**
     * Get workspace owners
     *
     * @param  int $workspaceId Workspace ID
     * @return array<int, array<string, mixed>> Owners data
     * @throws MondayApiException
     */
    public function getOwners(int $workspaceId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($workspaceId: ID!) {
            workspaces (ids: [$workspaceId]) {
                owners {
                    id
                    name
                    email
                    created_at
                    updated_at
                    state
                    photo_thumb
                    photo_thumb_small
                    photo_original
                    url
                    title
                    birthday
                    country_code
                    location
                    time_zone_identifier
                    phone
                    mobile_phone
                    is_admin
                    is_guest
                    is_pending
                    is_verified
                    join_date
                    sign_up_date
                }
            }
        }
        GRAPHQL;

        $variables = ['workspaceId' => $workspaceId];

        $this->logger->info('Getting workspace owners', ['workspace_id' => $workspaceId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['workspaces'][0]['owners'] ?? [];
    }
}
