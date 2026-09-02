<?php

namespace Tests\Feature\Api;

use App\Clients\UniversalisClient;
use App\Clients\XIVApiClient;
use App\DTOs\ItemPrice;
use App\Models\Analysis;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MarketControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolate the "analyze" rate limiter from other test classes sharing the array cache store.
        Cache::flush();
    }

    public function test_servers_endpoint_returns_only_active_servers(): void
    {
        Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA', 'is_active' => true]);
        Server::create(['name' => 'Retired', 'slug' => 'retired', 'datacenter' => 'Crystal', 'region' => 'NA', 'is_active' => false]);

        $response = $this->getJson('/api/v1/servers');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['slug' => 'balmung']);
        $response->assertJsonMissing(['slug' => 'retired']);
    }

    public function test_items_search_returns_empty_for_short_queries(): void
    {
        $response = $this->getJson('/api/v1/items/search?q=a');

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function test_items_search_delegates_to_xivapi_client(): void
    {
        $this->mock(XIVApiClient::class, function ($mock) {
            $mock->shouldReceive('searchItems')
                ->with('sword')
                ->once()
                ->andReturn([['ID' => 1, 'Name' => 'Iron Sword']]);
        });

        $response = $this->getJson('/api/v1/items/search?q=sword');

        $response->assertOk();
        $response->assertJsonFragment(['Name' => 'Iron Sword']);
    }

    public function test_prices_endpoint_returns_prices_for_the_given_items(): void
    {
        $price = new ItemPrice(
            itemId: 1001, minPriceNQ: 500, minPriceHQ: 0,
            medianSalePriceNQ: 500.0, medianSalePriceHQ: 0.0,
            averageSalePriceNQ: 500.0, recentPurchasePriceNQ: 500,
            regionMinPriceNQ: 500, regionMedianSalePriceNQ: 500.0,
            salesPerWeek: 5.0,
        );

        $this->mock(UniversalisClient::class, function ($mock) use ($price) {
            $mock->shouldReceive('getPrices')->with('balmung', ['1001'])->andReturn(['1001' => $price]);
        });

        $response = $this->getJson('/api/v1/prices/balmung/1001');

        $response->assertOk();
    }

    public function test_analyze_endpoint_validates_required_server(): void
    {
        $response = $this->postJson('/api/v1/analyze', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('server');
    }

    public function test_analyze_endpoint_rejects_unknown_server_slug(): void
    {
        $response = $this->postJson('/api/v1/analyze', ['server' => 'does-not-exist']);

        $response->assertStatus(422);
    }

    public function test_opportunities_endpoint_returns_404_for_unknown_server(): void
    {
        $response = $this->getJson('/api/v1/opportunities/does-not-exist');

        $response->assertNotFound();
    }

    public function test_opportunities_endpoint_returns_empty_array_when_no_completed_analysis(): void
    {
        $server = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);

        $response = $this->getJson("/api/v1/opportunities/{$server->slug}");

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function test_opportunities_endpoint_returns_latest_completed_analysis_results(): void
    {
        $server = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);
        Analysis::create([
            'server_id' => $server->id,
            'results'   => [['itemName' => 'Sword', 'profit' => 100]],
            'completed_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/opportunities/{$server->slug}");

        $response->assertOk();
        $response->assertJsonFragment(['itemName' => 'Sword']);
    }
}
