<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class DdosProtectionMiddlewareTest extends TestCase
{
    /**
     * Test that the DDoS protection middleware allows requests within the rate limit.
     */
    public function testMiddlewareAllowsRequestsWithinLimit(): void
    {
        // Send requests within the rate limit (e.g., 60 requests in this case)
        for ($i = 0; $i < 60; $i++) {
            $response = $this->get('/api/test'); // Replace '/' with the actual route you want to test
            $response->assertStatus(200); // Ensure each request succeeds
        }
    }

    /**
     * Test that the DDoS protection middleware blocks requests after the rate limit is exceeded.
     */
    public function testMiddlewareBlocksRequestsAfterLimitExceeded(): void
    {
        // Define rate limit parameters for testing
        $maxAttempts = 60;
        $key = 'ddos-protection:' . '127.0.0.1'; // Example IP address

        // Hit the route the maximum allowed times to reach the rate limit
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->get('/api/test');
        }

        // The next request should trigger the rate limit response (HTTP 429)
        $response = $this->get('/api/test');

        $response->assertStatus(429);
        $response->assertJson([
            'message' => 'Too many requests. Please slow down.',
        ]);

        // Clear the rate limiter after the test to avoid affecting other tests
        RateLimiter::clear($key);
    }
}
