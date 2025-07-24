<?php

namespace MondayV2SDK\Core;

use GuzzleHttp\Psr7\Response;

/**
 * Interface for Guzzle HTTP client operations
 */
interface GuzzleClientInterface
{
    /**
     * Send a POST request
     *
     * @param  string $uri     Request URI
     * @param  array  $options Request options
     * @return Response Response object
     */
    public function post(string $uri, array $options = []): Response;
}
