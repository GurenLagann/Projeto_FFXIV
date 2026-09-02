<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnalyzeRateLimitTest extends TestCase
{
    public function test_web_analyze_endpoint_is_throttled_after_six_requests_per_minute(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/analyze', []);
        }

        $response = $this->post('/analyze', []);

        $response->assertStatus(429);
    }

    public function test_api_analyze_endpoint_is_throttled_after_six_requests_per_minute(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/analyze', []);
        }

        $response = $this->postJson('/api/v1/analyze', []);

        $response->assertStatus(429);
    }
}
