<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\Core\Configuration\Configuration;
use MondayV2SDK\Core\Configuration\HttpClientConfig;
use MondayV2SDK\Core\Configuration\RateLimitConfig;
use MondayV2SDK\Core\Configuration\LoggingConfig;

class ConfigurationTest extends TestCase
{
    public function testHttpClientConfig(): void
    {
        $config = new HttpClientConfig('test-token', 60, 15, 'https://test.com');

        $this->assertEquals('test-token', $config->getApiToken());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertEquals(15, $config->getConnectTimeout());
        $this->assertEquals('https://test.com', $config->getBaseUrl());
    }

    public function testHttpClientConfigDefaults(): void
    {
        $config = new HttpClientConfig('test-token');

        $this->assertEquals('test-token', $config->getApiToken());
        $this->assertEquals(HttpClientConfig::DEFAULT_TIMEOUT, $config->getTimeout());
        $this->assertEquals(HttpClientConfig::DEFAULT_CONNECT_TIMEOUT, $config->getConnectTimeout());
        $this->assertEquals(HttpClientConfig::DEFAULT_API_BASE_URL, $config->getBaseUrl());
    }

    public function testHttpClientConfigFromArray(): void
    {
        $config = HttpClientConfig::fromArray(
            [
            'api_token' => 'test-token',
            'timeout' => 60,
            'connect_timeout' => 15,
            'base_url' => 'https://test.com'
            ]
        );

        $this->assertEquals('test-token', $config->getApiToken());
        $this->assertEquals(60, $config->getTimeout());
        $this->assertEquals(15, $config->getConnectTimeout());
        $this->assertEquals('https://test.com', $config->getBaseUrl());
    }

    public function testHttpClientConfigValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HttpClientConfig('');
    }

    public function testHttpClientConfigInvalidTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HttpClientConfig('test-token', 0);
    }

    public function testHttpClientConfigInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HttpClientConfig('test-token', 30, 10, 'invalid-url');
    }

    public function testRateLimitConfig(): void
    {
        $config = new RateLimitConfig(200, 2000, 120, 600, 20000);

        $this->assertEquals(200, $config->getMinuteLimit());
        $this->assertEquals(2000, $config->getDailyLimit());
        $this->assertEquals(120, $config->getRetryDelay());
        $this->assertEquals(600, $config->getCleanupInterval());
        $this->assertEquals(20000, $config->getMaxArraySize());
    }

    public function testRateLimitConfigDefaults(): void
    {
        $config = new RateLimitConfig();

        $this->assertEquals(RateLimitConfig::DEFAULT_MINUTE_LIMIT, $config->getMinuteLimit());
        $this->assertEquals(RateLimitConfig::DEFAULT_DAILY_LIMIT, $config->getDailyLimit());
        $this->assertEquals(RateLimitConfig::DEFAULT_RETRY_DELAY, $config->getRetryDelay());
        $this->assertEquals(RateLimitConfig::DEFAULT_CLEANUP_INTERVAL, $config->getCleanupInterval());
        $this->assertEquals(RateLimitConfig::DEFAULT_MAX_ARRAY_SIZE, $config->getMaxArraySize());
    }

    public function testRateLimitConfigFromArray(): void
    {
        $config = RateLimitConfig::fromArray(
            [
            'minute_limit' => 200,
            'daily_limit' => 2000,
            'retry_delay' => 120,
            'cleanup_interval' => 600,
            'max_array_size' => 20000
            ]
        );

        $this->assertEquals(200, $config->getMinuteLimit());
        $this->assertEquals(2000, $config->getDailyLimit());
        $this->assertEquals(120, $config->getRetryDelay());
        $this->assertEquals(600, $config->getCleanupInterval());
        $this->assertEquals(20000, $config->getMaxArraySize());
    }

    public function testRateLimitConfigValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RateLimitConfig(0);
    }

    public function testRateLimitConfigInvalidDailyLimit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RateLimitConfig(100, 50); // Daily limit less than minute limit
    }

    public function testLoggingConfig(): void
    {
        $config = new LoggingConfig(true, 'debug', '/var/log/monday.log');

        $this->assertTrue($config->isEnabled());
        $this->assertEquals('debug', $config->getLevel());
        $this->assertEquals('/var/log/monday.log', $config->getFile());
    }

    public function testLoggingConfigDefaults(): void
    {
        $config = new LoggingConfig();

        $this->assertEquals(LoggingConfig::DEFAULT_ENABLED, $config->isEnabled());
        $this->assertEquals(LoggingConfig::DEFAULT_LEVEL, $config->getLevel());
        $this->assertNull($config->getFile());
    }

    public function testLoggingConfigFromArray(): void
    {
        $config = LoggingConfig::fromArray(
            [
            'enabled' => true,
            'level' => 'debug',
            'file' => '/var/log/monday.log'
            ]
        );

        $this->assertTrue($config->isEnabled());
        $this->assertEquals('debug', $config->getLevel());
        $this->assertEquals('/var/log/monday.log', $config->getFile());
    }

    public function testLoggingConfigInvalidLevel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LoggingConfig(true, 'invalid-level');
    }

    public function testMainConfiguration(): void
    {
        $httpClient = new HttpClientConfig('test-token');
        $rateLimit = new RateLimitConfig();
        $logging = new LoggingConfig();

        $config = new Configuration($httpClient, $rateLimit, $logging);

        $this->assertSame($httpClient, $config->getHttpClient());
        $this->assertSame($rateLimit, $config->getRateLimit());
        $this->assertSame($logging, $config->getLogging());
        $this->assertEquals('test-token', $config->getApiToken());
    }

    public function testMainConfigurationFromArray(): void
    {
        $config = Configuration::fromArray(
            [
            'http_client' => [
                'api_token' => 'test-token',
                'timeout' => 60
            ],
            'rate_limit' => [
                'minute_limit' => 200
            ],
            'logging' => [
                'enabled' => true,
                'level' => 'debug'
            ]
            ]
        );

        $this->assertEquals('test-token', $config->getHttpClient()->getApiToken());
        $this->assertEquals(60, $config->getHttpClient()->getTimeout());
        $this->assertEquals(200, $config->getRateLimit()->getMinuteLimit());
        $this->assertTrue($config->getLogging()->isEnabled());
        $this->assertEquals('debug', $config->getLogging()->getLevel());
    }

    public function testMainConfigurationFromLegacyArray(): void
    {
        $config = Configuration::fromLegacyArray(
            [
            'api_token' => 'test-token',
            'minute_limit' => 200,
            'enabled' => true,
            'level' => 'debug'
            ]
        );

        $this->assertEquals('test-token', $config->getHttpClient()->getApiToken());
        $this->assertEquals(200, $config->getRateLimit()->getMinuteLimit());
        $this->assertTrue($config->getLogging()->isEnabled());
        $this->assertEquals('debug', $config->getLogging()->getLevel());
    }

    public function testMainConfigurationToArray(): void
    {
        $httpClient = new HttpClientConfig('test-token', 60);
        $rateLimit = new RateLimitConfig(200);
        $logging = new LoggingConfig(true, 'debug');

        $config = new Configuration($httpClient, $rateLimit, $logging);
        $array = $config->toArray();

        $this->assertEquals('test-token', $array['http_client']['api_token']);
        $this->assertEquals(60, $array['http_client']['timeout']);
        $this->assertEquals(200, $array['rate_limit']['minute_limit']);
        $this->assertTrue($array['logging']['enabled']);
        $this->assertEquals('debug', $array['logging']['level']);
    }

    public function testConfigurationBackwardCompatibility(): void
    {
        // Test that the new configuration system works with the old array format
        $legacyConfig = [
            'api_token' => 'test-token',
            'minute_limit' => 200,
            'daily_limit' => 2000,
            'enabled' => true,
            'level' => 'debug'
        ];

        $config = Configuration::fromLegacyArray($legacyConfig);

        // Verify all values are correctly extracted
        $this->assertEquals('test-token', $config->getApiToken());
        $this->assertEquals(200, $config->getRateLimit()->getMinuteLimit());
        $this->assertEquals(2000, $config->getRateLimit()->getDailyLimit());
        $this->assertTrue($config->getLogging()->isEnabled());
        $this->assertEquals('debug', $config->getLogging()->getLevel());
    }
}
