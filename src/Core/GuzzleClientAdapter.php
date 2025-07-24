<?php

namespace MondayV2SDK\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * Adapter for GuzzleHttp\Client to implement GuzzleClientInterface
 */
class GuzzleClientAdapter implements GuzzleClientInterface
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client($config);
    }

    public function post(string $uri, array $options = []): Response
    {
        return $this->client->post($uri, $options);
    }
} 