<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MondayV2SDK\Core\RateLimiter;
use MondayV2SDK\Core\Logger;

/**
 * Example demonstrating the RateLimiter's periodic cleanup functionality
 * 
 * This example shows how the RateLimiter automatically cleans up old data
 * to prevent memory leaks in long-running applications.
 */

echo "=== RateLimiter Periodic Cleanup Example ===\n\n";

// Create a rate limiter with custom cleanup settings
$rateLimiter = new RateLimiter([
    'minute_limit' => 10,
    'daily_limit' => 100,
    'cleanup_interval' => 60, // Cleanup every 60 seconds
    'max_array_size' => 50,   // Emergency cleanup if arrays exceed 50 items
    'logging' => [
        'enabled' => true,
        'level' => 'debug'
    ]
]);

echo "Initial configuration:\n";
$config = $rateLimiter->getCleanupConfig();
echo "- Cleanup interval: {$config['cleanup_interval']} seconds\n";
echo "- Max array size: {$config['max_array_size']} items\n";
echo "- Total cleanups: {$config['total_cleanups']}\n\n";

// Simulate some requests
echo "Making 5 requests...\n";
for ($i = 0; $i < 5; $i++) {
    $rateLimiter->checkLimit();
    usleep(100000); // 0.1 second delay
}

$stats = $rateLimiter->getUsageStats();
echo "After 5 requests:\n";
echo "- Minute requests: {$stats['minute_requests']}/{$stats['minute_limit']}\n";
echo "- Daily requests: {$stats['daily_requests']}/{$stats['daily_limit']}\n";
echo "- Minute remaining: {$stats['minute_remaining']}\n";
echo "- Daily remaining: {$stats['daily_remaining']}\n\n";

// Force a cleanup to demonstrate the functionality
echo "Forcing cleanup...\n";
$rateLimiter->forceCleanup();

$stats = $rateLimiter->getUsageStats();
$cleanupStats = $stats['cleanup_stats'];
$memoryStats = $stats['memory_stats'];

echo "After forced cleanup:\n";
echo "- Total cleanups: {$cleanupStats['total_cleanups']}\n";
echo "- Average cleanup time: {$cleanupStats['avg_cleanup_time_ms']}ms\n";
echo "- Last cleanup: {$cleanupStats['last_cleanup']}\n";
echo "- Request times array size: {$memoryStats['request_times_size']}\n";
echo "- Daily requests array size: {$memoryStats['daily_requests_size']}\n\n";

// Demonstrate memory management with large arrays
echo "Demonstrating emergency cleanup...\n";

// Use reflection to simulate large arrays (for demonstration purposes)
$reflection = new ReflectionClass($rateLimiter);
$requestTimesProperty = $reflection->getProperty('requestTimes');
$requestTimesProperty->setAccessible(true);

// Simulate a large array that would trigger emergency cleanup
$largeArray = range(time() - 1000, time(), 1); // 1000 timestamps
$requestTimesProperty->setValue($rateLimiter, $largeArray);

echo "Before emergency cleanup:\n";
$stats = $rateLimiter->getUsageStats();
echo "- Request times array size: {$stats['memory_stats']['request_times_size']}\n";
echo "- Max array size: {$stats['memory_stats']['max_array_size']}\n";

// Trigger emergency cleanup
$rateLimiter->forceCleanup();

echo "After emergency cleanup:\n";
$stats = $rateLimiter->getUsageStats();
echo "- Request times array size: {$stats['memory_stats']['request_times_size']}\n";
echo "- Total cleanups: {$stats['cleanup_stats']['total_cleanups']}\n\n";

// Show final statistics
echo "Final statistics:\n";
$finalStats = $rateLimiter->getUsageStats();
echo "- Minute requests: {$finalStats['minute_requests']}/{$finalStats['minute_limit']}\n";
echo "- Daily requests: {$finalStats['daily_requests']}/{$finalStats['daily_limit']}\n";
echo "- Total cleanups performed: {$finalStats['cleanup_stats']['total_cleanups']}\n";
echo "- Average cleanup time: {$finalStats['cleanup_stats']['avg_cleanup_time_ms']}ms\n";
echo "- Memory usage optimized: ✓\n\n";

echo "=== Example Complete ===\n";

/**
 * Key Benefits of Periodic Cleanup:
 * 
 * 1. **Memory Management**: Automatically removes old request timestamps
 *    and daily records to prevent memory leaks
 * 
 * 2. **Configurable Intervals**: Cleanup frequency can be adjusted based
 *    on application needs (default: every 5 minutes)
 * 
 * 3. **Emergency Cleanup**: Prevents arrays from growing beyond configured
 *    limits (default: 10,000 items)
 * 
 * 4. **Performance Monitoring**: Tracks cleanup statistics including
 *    duration and frequency
 * 
 * 5. **Production Ready**: Designed for long-running applications with
 *    minimal performance impact
 * 
 * 6. **Test Environment Support**: Special handling for test environments
 *    to avoid sleep() calls during testing
 */ 