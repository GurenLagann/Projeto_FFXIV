<?php

namespace Tests\Feature;

use App\Models\Analysis;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_history_page_lists_analyses(): void
    {
        $server = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);
        Analysis::create(['server_id' => $server->id, 'results' => []]);

        $response = $this->get('/history');

        $response->assertOk();
        $response->assertViewHas('analyses');
    }

    public function test_show_analysis_page_displays_a_single_analysis(): void
    {
        $server   = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);
        $analysis = Analysis::create(['server_id' => $server->id, 'results' => []]);

        $response = $this->get("/analysis/{$analysis->id}");

        $response->assertOk();
    }

    public function test_export_downloads_a_csv_of_the_analysis_results(): void
    {
        $server   = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);
        $analysis = Analysis::create([
            'server_id' => $server->id,
            'results'   => [[
                'itemName' => 'Iron Sword', 'profit' => 1000, 'costEstimate' => 500,
                'revenueEstimate' => 1500, 'marginPercent' => 66.666, 'salesPerWeek' => 12.3,
            ]],
        ]);

        $response = $this->get("/analysis/{$analysis->id}/export");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->streamedContent();
    }
}
