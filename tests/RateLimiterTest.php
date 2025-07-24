<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;
use MondayV2SDK\Exceptions\RateLimitException;

class RateLimiterTest extends TestCase
{
    private RateLimiter $rateLimiter;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger(['enabled' => false]); // Disable logging for tests
        $this->rateLimiter = new RateLimiter(
            [
            'minute_limit' => 5,
            'daily_limit' => 10,
            'cleanup_interval' => 60, // 1 minute for testing
            'max_array_size' => 100,
            'logging' => ['enabled' => false]
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->rateLimiter->reset();
    }

    public function testConstructorWithDefaultValues(): void
    {
        $rateLimiter = new RateLimiter();
        $stats = $rateLimiter->getUsageStats();

        $this->assertEquals(100, $stats['minute_limit']);
        $this->assertEquals(1000, $stats['daily_limit']);
        $this->assertEquals(300, $rateLimiter->getCleanupConfig()['cleanup_interval']);
        $this->assertEquals(10000, $rateLimiter->getCleanupConfig()['max_array_size']);
    }

    public function testConstructorWithCustomValues(): void
    {
        $rateLimiter = new RateLimiter(
            [
            'minute_limit' => 50,
            'daily_limit' => 500,
            'cleanup_interval' => 120,
            'max_array_size' => 5000,
            ]
        );

        $stats = $rateLimiter->getUsageStats();
        $config = $rateLimiter->getCleanupConfig();

        $this->assertEquals(50, $stats['minute_limit']);
        $this->assertEquals(500, $stats['daily_limit']);
        $this->assertEquals(120, $config['cleanup_interval']);
        $this->assertEquals(5000, $config['max_array_size']);
    }

    public function testBasicRateLimiting(): void
    {
        // Should allow requests within limits
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->checkLimit();
        }

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(5, $stats['minute_requests']);
        $this->assertEquals(5, $stats['daily_requests']);
        $this->assertEquals(0, $stats['minute_remaining']);
        $this->assertEquals(5, $stats['daily_remaining']);
    }

    public function testMinuteRateLimitExceeded(): void
    {
        // Make 5 requests (at the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->checkLimit();
        }

        // 6th request should trigger rate limiting
        $this->expectException(RateLimitException::class);
        $this->rateLimiter->checkLimit();
    }

    public function testDailyRateLimitExceeded(): void
    {
        // Create a rate limiter with higher minute limit to test daily limit
        $rateLimiter = new RateLimiter(
            [
            'minute_limit' => 20,
            'daily_limit' => 10,
            'logging' => ['enabled' => false]
            ]
        );

        // Make 10 requests (at the daily limit)
        for ($i = 0; $i < 10; $i++) {
            $rateLimiter->checkLimit();
        }

        // 11th request should trigger daily rate limiting
        $this->expectException(RateLimitException::class);
        $rateLimiter->checkLimit();
    }

    public function testRequestTimesCleanup(): void
    {
        // Simulate old requests by manipulating the internal state
        $reflection = new \ReflectionClass($this->rateLimiter);
        $requestTimesProperty = $reflection->getProperty('requestTimes');
        $requestTimesProperty->setAccessible(true);

        $oldTime = time() - 120; // 2 minutes ago
        $requestTimesProperty->setValue($this->rateLimiter, [$oldTime, $oldTime + 1, $oldTime + 2]);

        // Trigger cleanup
        $this->rateLimiter->checkLimit();

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(1, $stats['minute_requests']); // Only the new request should remain
    }

    public function testDailyRecordsCleanup(): void
    {
        // Simulate old daily records
        $reflection = new \ReflectionClass($this->rateLimiter);
        $dailyRequestsProperty = $reflection->getProperty('dailyRequests');
        $dailyRequestsProperty->setAccessible(true);

        $oldDate = date('Y-m-d', strtotime('-10 days'));
        $dailyRequestsProperty->setValue(
            $this->rateLimiter,
            [
            $oldDate => 50,
            date('Y-m-d') => 5
            ]
        );

        // Trigger cleanup
        $this->rateLimiter->checkLimit();

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(6, $stats['daily_requests']); // Only current day should remain
    }

    public function testPeriodicCleanup(): void
    {
        // Simulate that last cleanup was more than interval ago
        $reflection = new \ReflectionClass($this->rateLimiter);
        $lastCleanupProperty = $reflection->getProperty('lastCleanupTime');
        $lastCleanupProperty->setAccessible(true);
        $lastCleanupProperty->setValue($this->rateLimiter, time() - 120); // 2 minutes ago

        // Add some old data
        $requestTimesProperty = $reflection->getProperty('requestTimes');
        $requestTimesProperty->setAccessible(true);
        $requestTimesProperty->setValue($this->rateLimiter, [time() - 120, time() - 90, time() - 70]);

        // Trigger periodic cleanup
        $this->rateLimiter->forceCleanup();

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(0, $stats['minute_requests']); // All old requests should be cleaned up
        $this->assertGreaterThan(0, $stats['cleanup_stats']['total_cleanups']);
    }

    public function testEmergencyCleanup(): void
    {
        // Create a rate limiter with small max array size
        $rateLimiter = new RateLimiter(
            [
            'max_array_size' => 3,
            'minute_limit' => 10,
            'logging' => ['enabled' => false]
            ]
        );

        // Simulate large arrays
        $reflection = new \ReflectionClass($rateLimiter);
        $requestTimesProperty = $reflection->getProperty('requestTimes');
        $requestTimesProperty->setAccessible(true);

        // Fill with more than max size
        $largeArray = range(time() - 100, time(), 1);
        $requestTimesProperty->setValue($rateLimiter, $largeArray);

        // Trigger emergency cleanup
        $rateLimiter->forceCleanup();

        $stats = $rateLimiter->getUsageStats();
        $this->assertLessThanOrEqual(10, $stats['memory_stats']['request_times_size']); // Should be limited to minute_limit
    }

    public function testCleanupStatistics(): void
    {
        // Force multiple cleanups
        for ($i = 0; $i < 3; $i++) {
            $this->rateLimiter->forceCleanup();
        }

        $stats = $this->rateLimiter->getUsageStats();
        $cleanupStats = $stats['cleanup_stats'];

        $this->assertEquals(3, $cleanupStats['total_cleanups']);
        $this->assertGreaterThanOrEqual(0, $cleanupStats['avg_cleanup_time_ms']);
        $this->assertNotEmpty($cleanupStats['last_cleanup']);
        $this->assertGreaterThanOrEqual(0, $cleanupStats['next_cleanup_in']);
    }

    public function testForceCleanup(): void
    {
        // Add some old data
        $reflection = new \ReflectionClass($this->rateLimiter);
        $requestTimesProperty = $reflection->getProperty('requestTimes');
        $requestTimesProperty->setAccessible(true);
        $requestTimesProperty->setValue($this->rateLimiter, [time() - 120, time() - 90, time() - 30]);

        // Force cleanup
        $this->rateLimiter->forceCleanup();

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(1, $stats['minute_requests']); // Only recent request should remain
        $this->assertEquals(1, $stats['cleanup_stats']['total_cleanups']);
    }

    public function testGetCleanupConfig(): void
    {
        $config = $this->rateLimiter->getCleanupConfig();

        $this->assertEquals(60, $config['cleanup_interval']);
        $this->assertEquals(100, $config['max_array_size']);
        $this->assertEquals(0, $config['last_cleanup_time']);
        $this->assertEquals(0, $config['total_cleanups']);
    }

    public function testReset(): void
    {
        // Add some data
        $this->rateLimiter->checkLimit();
        $this->rateLimiter->forceCleanup();

        // Reset
        $this->rateLimiter->reset();

        $stats = $this->rateLimiter->getUsageStats();
        $config = $this->rateLimiter->getCleanupConfig();

        $this->assertEquals(0, $stats['minute_requests']);
        $this->assertEquals(0, $stats['daily_requests']);
        $this->assertEquals(0, $config['total_cleanups']);
        $this->assertEquals(0, $config['last_cleanup_time']);
    }

    public function testMemoryStats(): void
    {
        // Add some data
        for ($i = 0; $i < 3; $i++) {
            $this->rateLimiter->checkLimit();
        }

        $stats = $this->rateLimiter->getUsageStats();
        $memoryStats = $stats['memory_stats'];

        $this->assertEquals(3, $memoryStats['request_times_size']);
        $this->assertEquals(1, $memoryStats['daily_requests_size']); // Only current day
        $this->assertEquals(100, $memoryStats['max_array_size']);
    }

    public function testConcurrentAccessSimulation(): void
    {
        // Simulate rapid requests
        $startTime = microtime(true);

        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->checkLimit();
            usleep(1000); // 1ms delay
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(5, $stats['minute_requests']);
        $this->assertEquals(5, $stats['daily_requests']);

        // Should complete quickly (no rate limiting delays)
        $this->assertLessThan(100, $duration); // Should take less than 100ms
    }

    public function testRateLimitRecovery(): void
    {
        // Hit the minute limit
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->checkLimit();
        }

        // Should be at limit
        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(0, $stats['minute_remaining']);

        // Wait for recovery (simulate time passing)
        $reflection = new \ReflectionClass($this->rateLimiter);
        $requestTimesProperty = $reflection->getProperty('requestTimes');
        $requestTimesProperty->setAccessible(true);

        // Replace with old timestamps (more than 1 minute ago)
        $oldTimes = array_fill(0, 5, time() - 70);
        $requestTimesProperty->setValue($this->rateLimiter, $oldTimes);

        // Should be able to make requests again
        $this->rateLimiter->checkLimit();

        $stats = $this->rateLimiter->getUsageStats();
        $this->assertEquals(1, $stats['minute_requests']); // Only the new request
    }
}
