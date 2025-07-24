<?php

namespace MondayV2SDK\Core;

/**
 * Interface for HTTP client operations
 */
interface HttpClientInterface
{
    /**
     * Execute a GraphQL query
     *
     * @param  string               $query     GraphQL query
     * @param  array<string, mixed> $variables Query variables
     * @return array<string, mixed> Response data
     */
    public function query(string $query, array $variables = []): array;

    /**
     * Execute a GraphQL mutation
     *
     * @param  string               $mutation  GraphQL mutation
     * @param  array<string, mixed> $variables Mutation variables
     * @return array<string, mixed> Response data
     */
    public function mutate(string $mutation, array $variables = []): array;
}
