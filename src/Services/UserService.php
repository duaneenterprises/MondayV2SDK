<?php

namespace MondayV2SDK\Services;

use MondayV2SDK\Core\HttpClientInterface;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Exceptions\MondayApiException;

/**
 * Service for managing Monday.com users
 * 
 * Provides methods for querying user information and managing user-related data.
 */
class UserService
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
     * Get all users
     * 
     * @param  array<string, mixed> $options Query options
     * @return array<int, array<string, mixed>> Users data
     * @throws MondayApiException
     */
    public function getAll(array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query {
            users {
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
                account {
                    id
                    name
                    first_day_of_the_week
                    show_timeline_weekends
                    slug
                }
            }
        }
        GRAPHQL;

        $this->logger->info('Getting all users');

        $response = $this->httpClient->query($query);
        return $response['users'] ?? [];
    }

    /**
     * Get a user by ID
     * 
     * @param  int $userId User ID
     * @return array<string, mixed> User data
     * @throws MondayApiException
     */
    public function get(int $userId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($userId: ID!) {
            users (ids: [$userId]) {
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
                account {
                    id
                    name
                    first_day_of_the_week
                    show_timeline_weekends
                    slug
                }
            }
        }
        GRAPHQL;

        $variables = ['userId' => $userId];

        $this->logger->info('Getting user', ['user_id' => $userId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['users'][0] ?? [];
    }

    /**
     * Get current user
     * 
     * @return array<string, mixed> Current user data
     * @throws MondayApiException
     */
    public function getCurrent(): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query {
            me {
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
                account {
                    id
                    name
                    first_day_of_the_week
                    show_timeline_weekends
                    slug
                }
            }
        }
        GRAPHQL;

        $this->logger->info('Getting current user');

        $response = $this->httpClient->query($query);
        return $response['me'] ?? [];
    }

    /**
     * Get users by board
     * 
     * @param  int $boardId Board ID
     * @return array<int, array<string, mixed>> Users data
     * @throws MondayApiException
     */
    public function getByBoard(int $boardId): array
    {
        $this->rateLimiter->checkLimit();

        $query = <<<'GRAPHQL'
        query ($boardId: ID!) {
            boards (ids: [$boardId]) {
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

        $variables = ['boardId' => $boardId];

        $this->logger->info('Getting users by board', ['board_id' => $boardId]);

        $response = $this->httpClient->query($query, $variables);
        return $response['boards'][0]['subscribers'] ?? [];
    }

    /**
     * Search users
     * 
     * @param  string               $searchTerm Search term
     * @param  array<string, mixed> $options    Search options
     * @return array<int, array<string, mixed>> Matching users
     * @throws MondayApiException
     */
    public function search(string $searchTerm, array $options = []): array
    {
        $this->rateLimiter->checkLimit();

        if (empty($searchTerm)) {
            throw new \InvalidArgumentException('Search term is required');
        }

        $query = <<<'GRAPHQL'
        query ($searchTerm: String!) {
            users (search: $searchTerm) {
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
        GRAPHQL;

        $variables = ['searchTerm' => $searchTerm];

        $this->logger->info('Searching users', ['search_term' => $searchTerm]);

        $response = $this->httpClient->query($query, $variables);
        return $response['users'] ?? [];
    }
} 