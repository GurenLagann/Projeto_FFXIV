<?php

namespace Tests\Unit;

use App\DTOs\ItemPrice;
use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use App\Models\GatheringItem;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\RecipeLookup;
use App\Services\ProfitCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\TestCase;

class ProfitCalculatorTest extends TestCase
{
    private ProfitCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ProfitCalculator();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeItemPrice(int $itemId, array $attrs = []): ItemPrice
    {
        return new ItemPrice(
            itemId:                  $itemId,
            minPriceNQ:              $attrs['minPriceNQ']              ?? 1000,
            minPriceHQ:              $attrs['minPriceHQ']              ?? 1200,
            medianSalePriceNQ:       $attrs['medianSalePriceNQ']       ?? 900.0,
            medianSalePriceHQ:       $attrs['medianSalePriceHQ']       ?? 1100.0,
            averageSalePriceNQ:      $attrs['averageSalePriceNQ']      ?? 950.0,
            recentPurchasePriceNQ:   $attrs['recentPurchasePriceNQ']   ?? 1050,
            regionMinPriceNQ:        $attrs['regionMinPriceNQ']        ?? 850,
            regionMedianSalePriceNQ: $attrs['regionMedianSalePriceNQ'] ?? 870.0,
            salesPerWeek:            $attrs['salesPerWeek']            ?? 10.0,
        );
    }

    /**
     * Monta um Recipe em memória com o item resultado e materiais opcionais.
     *
     * @param  array<string, int>  $materials  [item_id => quantity]
     */
    private function makeRecipe(array $recipeAttrs = [], array $materials = [], ?RecipeLookup $lookup = null): Recipe
    {
        $itemId = $recipeAttrs['item_id'] ?? '1';

        $item = new Item(['id' => $itemId, 'name' => 'Test Item', 'icon' => '/icon.png']);
        $item->setRelation('recipeLookup', $lookup);

        $recipe = new Recipe(array_merge([
            'item_id'     => $itemId,
            'job_id'      => 9,
            'craft_level' => 1,
            'yields'      => 1,
            'can_be_hq'   => false,
            'stars'       => 0,
            'difficulty'  => 0,
        ], $recipeAttrs));

        $recipe->setRelation('item', $item);
        $recipe->setRelation('materials', $this->makeMaterialCollection($materials));

        return $recipe;
    }

    /** @param array<string, int> $materials [item_id => quantity] */
    private function makeMaterialCollection(array $materials): Collection
    {
        $collection = new Collection();

        foreach ($materials as $matId => $qty) {
            $mat = new Item(['id' => (string) $matId, 'name' => "Mat {$matId}"]);
            $mat->setRelation('gatheringItem', null);

            $pivot = new Pivot();
            $pivot->setRawAttributes(['quantity' => $qty]);
            $mat->setRelation('pivot', $pivot);

            $collection->push($mat);
        }

        return $collection;
    }

    private function makeGatheringItem(string $itemId): GatheringItem
    {
        $g = new GatheringItem(['item_id' => $itemId, 'gathering_level' => 1, 'stars' => 0, 'source' => 'gathering']);
        return $g;
    }

    // ─── Testes de cálculo básico ─────────────────────────────────────────────

    public function test_calculates_profit_correctly(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 2]);

        $finalPrice    = $this->makeItemPrice(1, ['minPriceNQ' => 10_000]);
        $materialPrice = $this->makeItemPrice(2, ['minPriceNQ' => 3_000]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $materialPrice],
            $finalPrice,
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Custo: 3000 × 2 = 6000, Receita: 10000, Lucro: 4000
        $this->assertEquals(6_000, $result->costEstimate);
        $this->assertEquals(10_000, $result->revenueEstimate);
        $this->assertEquals(4_000, $result->profit);
        $this->assertTrue($result->isProfitable);
    }

    public function test_calculates_margin_correctly(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 1]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $this->makeItemPrice(2, ['minPriceNQ' => 2_000])],
            $this->makeItemPrice(1, ['minPriceNQ' => 10_000]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Margem: (8000 / 10000) * 100 = 80%
        $this->assertEquals(80.0, $result->marginPercent);
    }

    public function test_negative_profit_when_cost_exceeds_revenue(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 5]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $this->makeItemPrice(2, ['minPriceNQ' => 3_000])],
            $this->makeItemPrice(1, ['minPriceNQ' => 5_000]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Custo: 15000, Receita: 5000, Lucro: -10000
        $this->assertEquals(-10_000, $result->profit);
        $this->assertFalse($result->isProfitable);
    }

    public function test_material_without_price_is_skipped(): void
    {
        // Material '2' tem preço, material '3' não tem
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 2, '3' => 1]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $this->makeItemPrice(2, ['minPriceNQ' => 1_000])],  // sem preço para '3'
            $this->makeItemPrice(1, ['minPriceNQ' => 5_000]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Custo ignora '3': 1000 × 2 = 2000
        $this->assertEquals(2_000, $result->costEstimate);
        $this->assertEquals(3_000, $result->profit);
    }

    public function test_yields_multiplies_revenue(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1', 'yields' => 3]);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1, ['minPriceNQ' => 1_000]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Receita: 1000 × 3 = 3000
        $this->assertEquals(3_000, $result->revenueEstimate);
        $this->assertEquals(3, $result->yieldsPerCraft);
    }

    public function test_zero_revenue_gives_zero_margin(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1, ['minPriceNQ' => 0]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertEquals(0.0, $result->marginPercent);
    }

    public function test_sales_per_week_comes_from_final_item_price(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1, ['salesPerWeek' => 42.5]),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertEquals(42.5, $result->salesPerWeek);
    }

    // ─── Testes de métricas ───────────────────────────────────────────────────

    public function test_cost_metric_median_sale_uses_median_price(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 1]);

        $matPrice = $this->makeItemPrice(2, ['minPriceNQ' => 500, 'medianSalePriceNQ' => 800.0]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $matPrice],
            $this->makeItemPrice(1, ['minPriceNQ' => 5_000]),
            CostMetric::MEDIAN_SALE,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Custo usa medianSalePriceNQ (800), não minPriceNQ (500)
        $this->assertEquals(800, $result->costEstimate);
    }

    public function test_revenue_metric_region_min_uses_region_min_price(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']);

        $finalPrice = $this->makeItemPrice(1, ['minPriceNQ' => 10_000, 'regionMinPriceNQ' => 7_000]);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $finalPrice,
            CostMetric::MIN_LISTING,
            RevenueMetric::REGION_MIN_LISTING,
        );

        // Receita usa regionMinPriceNQ (7000), não minPriceNQ (10000)
        $this->assertEquals(7_000, $result->revenueEstimate);
    }

    public function test_cost_metric_recent_sale_uses_recent_purchase_price(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1'], ['2' => 2]);

        $matPrice = $this->makeItemPrice(2, ['minPriceNQ' => 500, 'recentPurchasePriceNQ' => 1_200]);

        $result = $this->calculator->calculate(
            $recipe,
            ['2' => $matPrice],
            $this->makeItemPrice(1, ['minPriceNQ' => 5_000]),
            CostMetric::RECENT_SALE,
            RevenueMetric::HOME_MIN_LISTING,
        );

        // Custo usa recentPurchasePriceNQ (1200 × 2 = 2400)
        $this->assertEquals(2_400, $result->costEstimate);
    }

    // ─── Testes de craftJobs / gatheringItem ──────────────────────────────────

    public function test_craft_jobs_populated_from_recipe_lookup(): void
    {
        $lookup = new RecipeLookup();
        $lookup->setRawAttributes(['item_id' => '1', 'bsm_id' => 43, 'arm_id' => 189]);

        $recipe = $this->makeRecipe(['item_id' => '1'], [], $lookup);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertContains('BSM', $result->craftJobs);
        $this->assertContains('ARM', $result->craftJobs);
        $this->assertNotContains('CRP', $result->craftJobs);
    }

    public function test_craft_jobs_empty_when_no_recipe_lookup(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertEmpty($result->craftJobs);
    }

    public function test_all_mats_gatherable_when_every_material_has_gathering_item(): void
    {
        $recipe     = $this->makeRecipe(['item_id' => '1'], ['2' => 1, '3' => 2]);
        $materials  = $recipe->materials;

        $materials[0]->setRelation('gatheringItem', $this->makeGatheringItem('2'));
        $materials[1]->setRelation('gatheringItem', $this->makeGatheringItem('3'));

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertTrue($result->allMatsGatherable);
    }

    public function test_all_mats_gatherable_false_when_any_material_missing_gathering(): void
    {
        $recipe    = $this->makeRecipe(['item_id' => '1'], ['2' => 1, '3' => 2]);
        $materials = $recipe->materials;

        $materials[0]->setRelation('gatheringItem', $this->makeGatheringItem('2'));
        // '3' permanece null

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertFalse($result->allMatsGatherable);
    }

    public function test_all_mats_gatherable_false_when_no_materials(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']); // sem materiais

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertFalse($result->allMatsGatherable);
    }

    // ─── Testes de ProfitResult ───────────────────────────────────────────────

    public function test_result_contains_correct_item_metadata(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '99']);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(99),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $this->assertEquals(99, $result->itemId);
        $this->assertEquals('Test Item', $result->itemName);
        $this->assertEquals('https://xivapi.com/icon.png', $result->itemIcon);
    }

    public function test_json_serialize_includes_all_fields(): void
    {
        $recipe = $this->makeRecipe(['item_id' => '1']);

        $result = $this->calculator->calculate(
            $recipe,
            [],
            $this->makeItemPrice(1),
            CostMetric::MIN_LISTING,
            RevenueMetric::HOME_MIN_LISTING,
        );

        $serialized = $result->jsonSerialize();

        $this->assertArrayHasKey('itemId', $serialized);
        $this->assertArrayHasKey('profit', $serialized);
        $this->assertArrayHasKey('marginPercent', $serialized);
        $this->assertArrayHasKey('salesPerWeek', $serialized);
        $this->assertArrayHasKey('craftJobs', $serialized);
        $this->assertArrayHasKey('allMatsGatherable', $serialized);
    }
}
