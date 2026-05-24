<?php

namespace Tests\Feature;

use App\Clients\UniversalisClient;
use App\DTOs\ItemPrice;
use App\Models\Analysis;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\Server;
use App\Services\MarketAnalyzerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class MarketAnalyzerServiceTest extends TestCase
{
    use RefreshDatabase;

    private MarketAnalyzerService $service;
    private MockInterface $universalis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->universalis = $this->mock(UniversalisClient::class);
        $this->service     = app(MarketAnalyzerService::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeItemPrice(int $itemId, int $minPriceNQ = 1000, float $salesPerWeek = 10.0): ItemPrice
    {
        return new ItemPrice(
            itemId:                  $itemId,
            minPriceNQ:              $minPriceNQ,
            minPriceHQ:              0,
            medianSalePriceNQ:       (float) $minPriceNQ,
            medianSalePriceHQ:       0.0,
            averageSalePriceNQ:      (float) $minPriceNQ,
            recentPurchasePriceNQ:   $minPriceNQ,
            regionMinPriceNQ:        $minPriceNQ,
            regionMedianSalePriceNQ: (float) $minPriceNQ,
            salesPerWeek:            $salesPerWeek,
        );
    }

    private function createItem(string $id, string $name, bool $craftable = true): Item
    {
        return Item::create([
            'id'           => $id,
            'name'         => $name,
            'level'        => 1,
            'is_craftable' => $craftable,
        ]);
    }

    private function createRecipe(string $itemId, array $attrs = []): Recipe
    {
        return Recipe::create(array_merge([
            'item_id'     => $itemId,
            'job_id'      => 9,
            'craft_level' => 1,
            'yields'      => 1,
            'can_be_hq'   => false,
            'stars'       => 0,
            'difficulty'  => 0,
        ], $attrs));
    }

    private function createServer(string $name = 'Balmung'): Server
    {
        return Server::create([
            'name'       => $name,
            'slug'       => strtolower($name),
            'datacenter' => 'Crystal',
            'region'     => 'NA',
            'is_active'  => true,
        ]);
    }

    // ─── analyze() ────────────────────────────────────────────────────────────

    public function test_analyze_returns_empty_when_no_recipes_in_db(): void
    {
        $this->universalis->shouldReceive('getPrices')->andReturn([]);

        $results = $this->service->analyze('Balmung');

        $this->assertEmpty($results);
    }

    public function test_analyze_returns_results_sorted_by_profit_descending(): void
    {
        $item1 = $this->createItem('1001', 'Cheap Sword');
        $item2 = $this->createItem('1002', 'Expensive Shield');
        $mat   = $this->createItem('2001', 'Iron Ore');

        $recipe1 = $this->createRecipe('1001');
        $recipe2 = $this->createRecipe('1002');
        $recipe1->materials()->attach('2001', ['quantity' => 1]);
        $recipe2->materials()->attach('2001', ['quantity' => 1]);

        // Sword: 5000 - 1000 = 4000 lucro
        // Shield: 10000 - 1000 = 9000 lucro
        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),
            '1002' => $this->makeItemPrice(1002, 10_000),
            '2001' => $this->makeItemPrice(2001, 1_000),
        ]);

        $results = $this->service->analyze('Balmung');

        $this->assertCount(2, $results);
        $this->assertEquals('Expensive Shield', $results[0]->itemName);
        $this->assertEquals('Cheap Sword', $results[1]->itemName);
        $this->assertGreaterThan($results[1]->profit, $results[0]->profit);
    }

    public function test_analyze_skips_items_without_price(): void
    {
        $this->createItem('1001', 'Iron Ingot');
        $this->createRecipe('1001');

        // Nenhum preço retornado para o item
        $this->universalis->shouldReceive('getPrices')->andReturn([]);

        $results = $this->service->analyze('Balmung');

        $this->assertEmpty($results);
    }

    public function test_analyze_filters_by_job_id(): void
    {
        $this->createItem('1001', 'BSM Item');
        $this->createItem('1002', 'ARM Item');
        $this->createRecipe('1001', ['job_id' => 9]);   // Blacksmith
        $this->createRecipe('1002', ['job_id' => 10]);  // Armorer

        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),
            '1002' => $this->makeItemPrice(1002, 5_000),
        ]);

        $results = $this->service->analyze('Balmung', ['job_id' => 9]);

        $this->assertCount(1, $results);
        $this->assertEquals('BSM Item', $results[0]->itemName);
    }

    public function test_analyze_filters_by_min_level(): void
    {
        $this->createItem('1001', 'Low Level');
        $this->createItem('1002', 'High Level');
        $this->createRecipe('1001', ['craft_level' => 10]);
        $this->createRecipe('1002', ['craft_level' => 50]);

        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),
            '1002' => $this->makeItemPrice(1002, 5_000),
        ]);

        $results = $this->service->analyze('Balmung', ['min_level' => 30]);

        $this->assertCount(1, $results);
        $this->assertEquals('High Level', $results[0]->itemName);
    }

    public function test_analyze_filters_by_max_level(): void
    {
        $this->createItem('1001', 'Low Level');
        $this->createItem('1002', 'High Level');
        $this->createRecipe('1001', ['craft_level' => 10]);
        $this->createRecipe('1002', ['craft_level' => 80]);

        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),
            '1002' => $this->makeItemPrice(1002, 5_000),
        ]);

        $results = $this->service->analyze('Balmung', ['max_level' => 50]);

        $this->assertCount(1, $results);
        $this->assertEquals('Low Level', $results[0]->itemName);
    }

    public function test_analyze_filters_by_min_profit(): void
    {
        $item1 = $this->createItem('1001', 'Low Profit');
        $item2 = $this->createItem('1002', 'High Profit');
        $mat   = $this->createItem('2001', 'Material');
        $this->createRecipe('1001')->materials()->attach('2001', ['quantity' => 1]);
        $this->createRecipe('1002')->materials()->attach('2001', ['quantity' => 1]);

        // Low Profit: 2000 - 1000 = 1000
        // High Profit: 10000 - 1000 = 9000
        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 2_000),
            '1002' => $this->makeItemPrice(1002, 10_000),
            '2001' => $this->makeItemPrice(2001, 1_000),
        ]);

        $results = $this->service->analyze('Balmung', ['min_profit' => 5_000]);

        $this->assertCount(1, $results);
        $this->assertEquals('High Profit', $results[0]->itemName);
    }

    public function test_analyze_filters_by_min_margin(): void
    {
        $mat = $this->createItem('2001', 'Material');

        $this->createItem('1001', 'Low Margin');
        $this->createRecipe('1001')->materials()->attach('2001', ['quantity' => 1]);

        $this->createItem('1002', 'High Margin');
        $this->createRecipe('1002')->materials()->attach('2001', ['quantity' => 1]);

        // Low Margin: 1100 - 1000 = 100 → 9%
        // High Margin: 5000 - 1000 = 4000 → 80%
        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 1_100),
            '1002' => $this->makeItemPrice(1002, 5_000),
            '2001' => $this->makeItemPrice(2001, 1_000),
        ]);

        $results = $this->service->analyze('Balmung', ['min_margin' => 50.0]);

        $this->assertCount(1, $results);
        $this->assertEquals('High Margin', $results[0]->itemName);
    }

    public function test_analyze_filters_by_min_sales(): void
    {
        $this->createItem('1001', 'Slow Seller');
        $this->createItem('1002', 'Fast Seller');
        $this->createRecipe('1001');
        $this->createRecipe('1002');

        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000, salesPerWeek: 2.0),
            '1002' => $this->makeItemPrice(1002, 5_000, salesPerWeek: 20.0),
        ]);

        $results = $this->service->analyze('Balmung', ['min_sales' => 10.0]);

        $this->assertCount(1, $results);
        $this->assertEquals('Fast Seller', $results[0]->itemName);
    }

    public function test_analyze_respects_limit(): void
    {
        foreach (range(1, 5) as $i) {
            $this->createItem("100{$i}", "Item {$i}");
            $this->createRecipe("100{$i}");
        }

        $prices = [];
        foreach (range(1, 5) as $i) {
            $prices["100{$i}"] = $this->makeItemPrice(intval("100{$i}"), 5_000);
        }
        $this->universalis->shouldReceive('getPrices')->andReturn($prices);

        $results = $this->service->analyze('Balmung', ['limit' => 3]);

        $this->assertCount(3, $results);
    }

    public function test_analyze_uses_default_metrics_when_not_specified(): void
    {
        $this->createItem('1001', 'Test Item');
        $mat = $this->createItem('2001', 'Material');
        $this->createRecipe('1001')->materials()->attach('2001', ['quantity' => 1]);

        // Métricas padrão: CostMetric::MIN_LISTING e RevenueMetric::HOME_MIN_LISTING
        // Ambas usam minPriceNQ
        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),
            '2001' => $this->makeItemPrice(2001, 1_000),
        ]);

        $results = $this->service->analyze('Balmung');

        $this->assertCount(1, $results);
        $this->assertEquals(4_000, $results[0]->profit);
    }

    // ─── saveAnalysis() ───────────────────────────────────────────────────────

    public function test_save_analysis_persists_to_database(): void
    {
        $server  = $this->createServer('Gilgamesh');
        $filters = ['job_id' => 9, 'min_profit' => 5000];

        $this->universalis->shouldReceive('getPrices')->andReturn([]);
        $results = $this->service->analyze('gilgamesh', $filters);

        $analysis = $this->service->saveAnalysis($server, $filters, $results, 1234);

        $this->assertDatabaseHas('analyses', [
            'id'        => $analysis->id,
            'server_id' => $server->id,
        ]);

        $fresh = Analysis::find($analysis->id);
        $this->assertEquals(1234, $fresh->execution_time);
        $this->assertEquals($filters, $fresh->filters);
        $this->assertNotNull($fresh->completed_at);
    }

    public function test_save_analysis_counts_profitable_opportunities(): void
    {
        $server = $this->createServer();

        $this->createItem('1001', 'Profitable');
        $this->createItem('1002', 'Unprofitable');
        $mat = $this->createItem('2001', 'Material');
        $this->createRecipe('1001')->materials()->attach('2001', ['quantity' => 1]);
        $this->createRecipe('1002')->materials()->attach('2001', ['quantity' => 1]);

        $this->universalis->shouldReceive('getPrices')->andReturn([
            '1001' => $this->makeItemPrice(1001, 5_000),   // lucro: 4000
            '1002' => $this->makeItemPrice(1002, 500),     // lucro: -500
            '2001' => $this->makeItemPrice(2001, 1_000),
        ]);

        $results  = $this->service->analyze(strtolower($server->slug));
        $analysis = $this->service->saveAnalysis($server, [], $results, 0);

        $this->assertEquals(1, $analysis->total_opportunities);
    }

    // ─── getCurrentPrices() ───────────────────────────────────────────────────

    public function test_get_current_prices_delegates_to_universalis(): void
    {
        $expected = ['5057' => $this->makeItemPrice(5057, 8_000)];

        $this->universalis
            ->shouldReceive('getPrices')
            ->once()
            ->with('Balmung', ['5057'])
            ->andReturn($expected);

        $result = $this->service->getCurrentPrices('Balmung', ['5057']);

        $this->assertSame($expected, $result);
    }
}
