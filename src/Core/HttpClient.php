<?php

namespace MondayV2SDK\Core;

use GuzzleHttp\Client;
use MondayV2SDK\Core\GuzzleClientInterface;
use MondayV2SDK\Core\GuzzleClientAdapter;
use GuzzleHttp\Exception\RequestException;
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;

/**
 * HTTP client for Monday.com GraphQL API
 *
 * Handles all HTTP communication with Monday.com's GraphQL endpoint,
 * including authentication, request formatting, and response parsing.
 */
class HttpClient implements HttpClientInterface
{
    private const API_URL = 'https://api.monday.com/v2';
    private const API_VERSION = '2023-10';

    private GuzzleClientInterface $guzzleClient;
    private string $apiToken;
    /**
     * @var array<string, mixed>
     */
    private array $config;
    private Logger $logger;

    /**
     * Constructor
     *
     * @param string               $apiToken Monday.com API token
     * @param array<string, mixed> $config   Configuration options
     */
    public function __construct(string $apiToken, array $config = [])
    {
        $this->apiToken = $apiToken;
        $this->config = $config;
        $this->logger = new Logger($config['logging'] ?? []);

        $this->initializeGuzzleClient();
    }

    /**
     * Initialize Guzzle HTTP client
     */
    private function initializeGuzzleClient(): void
    {
        $this->guzzleClient = new GuzzleClientAdapter(
            [
            'base_uri' => self::API_URL,
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
                'Authorization' => $this->apiToken,
                'Content-Type' => 'application/json',
                'API-Version' => self::API_VERSION,
            ],
            'http_errors' => false,
            ]
        );
    }

    /**
     * Execute a GraphQL query
     *
     * @param  string               $query     GraphQL query
     * @param  array<string, mixed> $variables Query variables
     * @return array<string, mixed> Response data
     * @throws MondayApiException
     */
    public function query(string $query, array $variables = []): array
    {
        return $this->executeGraphQL($query, $variables);
    }

    /**
     * Execute a GraphQL mutation
     *
     * @param  string               $mutation  GraphQL mutation
     * @param  array<string, mixed> $variables Mutation variables
     * @return array<string, mixed> Response data
     * @throws MondayApiException
     */
    public function mutate(string $mutation, array $variables = []): array
    {
        return $this->executeGraphQL($mutation, $variables);
    }

    /**
     * Execute GraphQL request
     *
     * @param  string               $graphql   GraphQL query or mutation
     * @param  array<string, mixed> $variables Variables
     * @return array<string, mixed> Response data
     * @throws MondayApiException
     */
    private function executeGraphQL(string $graphql, array $variables = []): array
    {
        $payload = [
            'query' => $graphql,
            'variables' => $variables,
        ];

        $this->logger->info(
            'Executing GraphQL request',
            [
            'query' => $graphql,
            'variables' => $variables,
            ]
        );

        try {
            $response = $this->guzzleClient->post(
                '',
                [
                'json' => $payload,
                ]
            );

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            // Handle JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new MondayApiException(
                    "Invalid JSON response: " . json_last_error_msg(),
                    $statusCode,
                    []
                );
            }

            $this->logger->info(
                'GraphQL response received',
                [
                'status_code' => $statusCode,
                'response_size' => strlen($body),
                ]
            );

            // Handle rate limiting first (before HTTP errors)
            if ($this->isRateLimited($response)) {
                $retryAfter = $this->getRetryAfter($response);
                throw new RateLimitException(
                    "Rate limit exceeded. Retry after {$retryAfter} seconds.",
                    $retryAfter
                );
            }

            // Handle HTTP errors
            if ($statusCode !== 200) {
                throw new MondayApiException(
                    "HTTP error: {$statusCode}",
                    $statusCode,
                    $data ?? []
                );
            }

            // Handle GraphQL errors
            if (isset($data['errors']) && !empty($data['errors'])) {
                $this->handleGraphQLErrors($data['errors']);
            }

            return $data['data'] ?? [];
        } catch (RequestException $e) {
            $this->logger->error(
                'HTTP request failed',
                [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                ]
            );

            throw new MondayApiException(
                "HTTP request failed: {$e->getMessage()}",
                $e->getCode(),
                []
            );
        }
    }

    /**
     * Handle GraphQL errors from response
     *
     * @param  array<int, array<string, mixed>> $errors GraphQL errors
     * @throws MondayApiException
     */
    private function handleGraphQLErrors(array $errors): void
    {
        $errorMessages = [];

        foreach ($errors as $error) {
            $message = $error['message'] ?? 'Unknown GraphQL error';
            $extensions = $error['extensions'] ?? [];

            $errorMessages[] = $message;

            $this->logger->error(
                'GraphQL error',
                [
                'message' => $message,
                'extensions' => $extensions,
                ]
            );
        }

        throw new MondayApiException(
            'GraphQL errors: ' . implode('; ', $errorMessages),
            0,
            ['errors' => $errors]
        );
    }

    /**
     * Check if response indicates rate limiting
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return bool
     */
    private function isRateLimited($response): bool
    {
        return $response->getStatusCode() === 429;
    }

    /**
     * Get retry-after value from response headers
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return int
     */
    private function getRetryAfter($response): int
    {
        $retryAfter = $response->getHeaderLine('Retry-After');
        return (int) ($retryAfter ?: 60);
    }

    /**
     * Get Guzzle client (for testing)
     *
     * @return GuzzleClientInterface
     */
    public function getGuzzleClient(): GuzzleClientInterface
    {
        return $this->guzzleClient;
    }

    /**
     * Get logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Format a phone number to standard format
     *
     * @param  string $phoneNumber The phone number to format
     * @return string|null Formatted phone number or null if invalid
     */
    public function formatPhoneNumber(string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        // Remove all non-digit characters except + and spaces
        $cleaned = preg_replace('/[^\d\s\+]/', '', $phoneNumber);

        // Remove spaces - ensure $cleaned is a string
        $cleaned = str_replace(' ', '', (string) $cleaned);

        // Check if it starts with +1 (US country code)
        if (preg_match('/^\+1(\d{10})$/', $cleaned, $matches)) {
            $formatted = '+1' . $matches[1];
            $this->logger->info(
                'Formatted phone number with country code',
                [
                'original' => $phoneNumber,
                'formatted' => $formatted
                ]
            );
            return $formatted;
        }

        // Check if it starts with 1 and has 11 digits total (US number without +)
        if (preg_match('/^1(\d{10})$/', $cleaned, $matches)) {
            $formatted = '+1' . $matches[1];
            $this->logger->info(
                'Formatted phone number with country code',
                [
                'original' => $phoneNumber,
                'formatted' => $formatted
                ]
            );
            return $formatted;
        }

        // Check if it's exactly 10 digits (US number without country code)
        if (preg_match('/^(\d{10})$/', $cleaned, $matches)) {
            $formatted = $matches[1];
            $this->logger->info(
                'Formatted phone number without country code',
                [
                'original' => $phoneNumber,
                'formatted' => $formatted
                ]
            );
            return $formatted;
        }

        // If it doesn't match any pattern, return the cleaned version if it has at least 10 digits
        if (strlen($cleaned) >= 10) {
            // Extract the last 10 digits
            $lastTen = substr($cleaned, -10);
            if (preg_match('/^\d{10}$/', $lastTen)) {
                $formatted = $lastTen;
                $this->logger->info(
                    'Extracted last 10 digits from phone number',
                    [
                    'original' => $phoneNumber,
                    'formatted' => $formatted
                    ]
                );
                return $formatted;
            }
        }

        $this->logger->warning(
            'Could not format phone number',
            [
            'phone_number' => $phoneNumber
            ]
        );
        return null;
    }
}
