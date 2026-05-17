<?php

namespace App\Services;

use App\DTOs\ItemPrice;
use App\DTOs\ProfitResult;
use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use App\Models\Recipe;

class ProfitCalculator
{
    public function calculate(
        Recipe        $recipe,
        array         $materialPrices,
        ItemPrice     $finalItemPrice,
        CostMetric    $costMetric,
        RevenueMetric $revenueMetric,
    ): ProfitResult {
        $costField    = $costMetric->getUniversalisField();
        $revenueField = $revenueMetric->getUniversalisField();

        $craftCost = $this->calculateMaterialCost($recipe, $materialPrices, $costField);
        $revenue   = $this->calculateRevenue($finalItemPrice, $revenueField, $recipe->yields);
        $profit    = $revenue - $craftCost;
        $margin    = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

        $craftJobs = $recipe->item->recipeLookup?->getJobAbbreviations() ?? [];

        $allMatsGatherable = $recipe->materials->isNotEmpty()
            && $recipe->materials->every(fn($mat) => $mat->gatheringItem !== null);

        return new ProfitResult(
            itemId:             (int) $recipe->item_id,
            itemName:           $recipe->item->name ?? "Item #{$recipe->item_id}",
            itemIcon:           $recipe->item->icon ?? null,
            profit:             $profit,
            costEstimate:       $craftCost,
            revenueEstimate:    $revenue,
            yieldsPerCraft:     $recipe->yields,
            salesPerWeek:       $finalItemPrice->salesPerWeek,
            marginPercent:      $margin,
            isProfitable:       $profit > 0,
            craftJobs:          $craftJobs,
            allMatsGatherable:  $allMatsGatherable,
        );
    }

    private function calculateMaterialCost(Recipe $recipe, array $materialPrices, string $field): int
    {
        $total = 0;

        foreach ($recipe->materials as $material) {
            $qty   = $material->pivot->quantity;
            $price = $materialPrices[$material->id] ?? null;

            if (!$price instanceof ItemPrice) {
                continue;
            }

            $total += (int) ($price->getFieldValue($field) * $qty);
        }

        return $total;
    }

    private function calculateRevenue(ItemPrice $price, string $field, int $yields): int
    {
        return (int) ($price->getFieldValue($field) * $yields);
    }
}
