<?php

namespace App\Services;

use App\Clients\UniversalisClient;
use App\Clients\XIVApiClient;
use App\DTOs\ProfitResult;
use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use App\Models\Analysis;
use App\Models\Recipe;
use App\Models\Server;

class MarketAnalyzerService
{
    public function __construct(
        private UniversalisClient $universalis,
        private XIVApiClient      $xivapi,
        private ProfitCalculator  $calculator,
    ) {}

    public function analyze(string $server, array $filters = []): array
    {
        $costMetric    = CostMetric::tryFrom($filters['cost_metric'] ?? '') ?? CostMetric::MIN_LISTING;
        $revenueMetric = RevenueMetric::tryFrom($filters['revenue_metric'] ?? '') ?? RevenueMetric::HOME_MIN_LISTING;

        $query = Recipe::with([
            'item:id,name,icon',
            'item.recipeLookup:item_id,crp_id,bsm_id,arm_id,gsm_id,ltw_id,wvr_id,alc_id,cul_id',
            'materials:id,name',
            'materials.gatheringItem:item_id,gathering_level,stars,source',
        ]);

        if (!empty($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }

        if (!empty($filters['min_level'])) {
            $query->where('craft_level', '>=', $filters['min_level']);
        }

        if (!empty($filters['max_level'])) {
            $query->where('craft_level', '<=', $filters['max_level']);
        }

        if (isset($filters['stars'])) {
            $query->where('stars', $filters['stars']);
        }

        $recipes = $query->get();

        if ($recipes->isEmpty()) {
            return [];
        }

        $itemIds = $recipes->pluck('item_id')->unique()->values()->all();
        $materialIds = $recipes->flatMap(fn($r) => $r->materials->pluck('id'))->unique()->values()->all();
        $allIds = array_unique(array_merge($itemIds, $materialIds));

        $prices = $this->universalis->getPrices($server, $allIds);

        $results = [];

        foreach ($recipes as $recipe) {
            $finalPrice = $prices[$recipe->item_id] ?? null;
            if (!$finalPrice) {
                continue;
            }

            $materialPrices = [];
            foreach ($recipe->materials as $material) {
                if (isset($prices[$material->id])) {
                    $materialPrices[$material->id] = $prices[$material->id];
                }
            }

            $result = $this->calculator->calculate($recipe, $materialPrices, $finalPrice, $costMetric, $revenueMetric);

            if (!empty($filters['min_profit']) && $result->profit < $filters['min_profit']) {
                continue;
            }

            if (!empty($filters['min_margin']) && $result->marginPercent < $filters['min_margin']) {
                continue;
            }

            if (!empty($filters['min_sales']) && $result->salesPerWeek < $filters['min_sales']) {
                continue;
            }

            if (!empty($filters['gatherable_only']) && !$result->allMatsGatherable) {
                continue;
            }

            $results[] = $result;
        }

        usort($results, fn(ProfitResult $a, ProfitResult $b) => $b->profit <=> $a->profit);

        $limit = (int) ($filters['limit'] ?? config('market.analysis.max_results', 1000));

        return array_slice($results, 0, $limit);
    }

    public function saveAnalysis(Server $server, array $filters, array $results, int $executionTime): Analysis
    {
        return Analysis::create([
            'server_id'           => $server->id,
            'filters'             => $filters,
            'results'             => array_map(fn($r) => $r->jsonSerialize(), $results),
            'execution_time'      => $executionTime,
            'total_opportunities' => count(array_filter($results, fn($r) => $r->isProfitable)),
            'completed_at'        => now(),
        ]);
    }

    public function getCurrentPrices(string $server, array $itemIds): array
    {
        return $this->universalis->getPrices($server, $itemIds);
    }
}
