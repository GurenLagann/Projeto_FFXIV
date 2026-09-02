<?php

namespace Tests\Feature;

use App\Clients\UniversalisClient;
use App\DTOs\ItemPrice;
use App\Livewire\MarketAnalyzer;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketAnalyzerLivewireTest extends TestCase
{
    use RefreshDatabase;

    private function makeItemPrice(int $itemId, int $minPriceNQ): ItemPrice
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
            salesPerWeek:            50.0,
        );
    }

    public function test_results_are_not_exposed_as_a_public_wire_property(): void
    {
        $properties = (new \ReflectionClass(MarketAnalyzer::class))
            ->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->assertFalse(
            collect($properties)->pluck('name')->contains('results'),
            'results must not be a public property — it gets re-serialized into every Livewire wire payload.'
        );
    }

    public function test_run_analysis_computes_results_and_makes_them_available_for_pagination(): void
    {
        $server   = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);
        $item     = Item::create(['id' => '1001', 'name' => 'Sword', 'is_craftable' => true]);
        $material = Item::create(['id' => '2001', 'name' => 'Iron Ore']);
        $recipe   = Recipe::create([
            'item_id' => '1001', 'job_id' => 9, 'craft_level' => 1,
            'yields' => 1, 'can_be_hq' => false, 'stars' => 0, 'difficulty' => 0,
        ]);
        $recipe->materials()->attach('2001', ['quantity' => 1]);

        $this->mock(UniversalisClient::class, function ($mock) {
            $mock->shouldReceive('getPrices')->andReturn([
                '1001' => $this->makeItemPrice(1001, 10_000),
                '2001' => $this->makeItemPrice(2001, 1_000),
            ]);
        });

        $component = Livewire::test(MarketAnalyzer::class)
            ->set('serverId', $server->id)
            ->call('runAnalysis');

        $component->assertSet('analyzed', true);
        $component->assertSet('totalResults', 1);

        $paged = $component->instance()->pagedResults;
        $this->assertSame(1, $paged->total());
        $this->assertSame('Sword', $paged->items()[0]['itemName']);
    }
}
