<?php

namespace Tests\Feature;

use App\Jobs\FetchMarketDataJob;
use App\Models\Analysis;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FetchMarketDataJobFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_marks_the_analysis_as_failed_with_the_error_message(): void
    {
        $server   = Server::create([
            'name' => 'Balmung', 'slug' => 'balmung',
            'datacenter' => 'Crystal', 'region' => 'NA',
        ]);
        $analysis = Analysis::create(['server_id' => $server->id, 'filters' => []]);

        $job = new FetchMarketDataJob('balmung', [], $analysis->id);
        $job->failed(new \RuntimeException('Universalis timed out'));

        $analysis->refresh();

        $this->assertNotNull($analysis->failed_at);
        $this->assertSame('Universalis timed out', $analysis->error);
    }
}
