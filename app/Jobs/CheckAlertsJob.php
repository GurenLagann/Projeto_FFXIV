<?php

namespace App\Jobs;

use App\Clients\UniversalisClient;
use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use App\Mail\AlertTriggeredMail;
use App\Models\Alert;
use App\Services\ProfitCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class CheckAlertsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    // Evita re-notificar o mesmo alerta em menos de 24h
    private const COOLDOWN_HOURS = 24;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(UniversalisClient $universalis, ProfitCalculator $calculator): void
    {
        $alerts = Alert::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', now()->subHours(self::COOLDOWN_HOURS));
            })
            ->with([
                'user',
                'server',
                'item.recipes.materials.gatheringItem',
                'item.recipes.item.recipeLookup',
            ])
            ->get();

        // Agrupa por servidor para fazer uma única chamada à API por servidor
        $byServer = $alerts->groupBy(fn(Alert $a) => $a->server->slug);

        foreach ($byServer as $serverSlug => $serverAlerts) {
            try {
                $this->checkServerAlerts($serverSlug, $serverAlerts, $universalis, $calculator);
            } catch (\Throwable $e) {
                \Log::error('CheckAlertsJob: falha ao verificar alertas do servidor', [
                    'server' => $serverSlug,
                    'error'  => $e->getMessage(),
                ]);
            }
        }
    }

    private function checkServerAlerts(
        string $serverSlug,
        \Illuminate\Support\Collection $serverAlerts,
        UniversalisClient $universalis,
        ProfitCalculator $calculator,
    ): void {
        $itemIds = $serverAlerts->flatMap(function (Alert $alert) {
            $recipe = $alert->item->recipes->first();
            if (!$recipe) {
                return [$alert->item_id];
            }
            return array_merge([$alert->item_id], $recipe->materials->pluck('id')->all());
        })->unique()->values()->all();

        $prices = $universalis->getPrices($serverSlug, $itemIds);

        foreach ($serverAlerts as $alert) {
            $recipe = $alert->item->recipes->first();
            if (!$recipe) {
                continue;
            }

            $finalPrice = $prices[$alert->item_id] ?? null;
            if (!$finalPrice) {
                continue;
            }

            $materialPrices = [];
            foreach ($recipe->materials as $material) {
                if (isset($prices[$material->id])) {
                    $materialPrices[$material->id] = $prices[$material->id];
                }
            }

            $result = $calculator->calculate(
                $recipe,
                $materialPrices,
                $finalPrice,
                CostMetric::MIN_LISTING,
                RevenueMetric::HOME_MIN_LISTING,
            );

            if ($result->profit >= $alert->min_profit && $result->marginPercent >= $alert->min_margin) {
                Mail::to($alert->user)->send(new AlertTriggeredMail($alert, $result));
                $alert->update(['last_notified_at' => now()]);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('CheckAlertsJob failed', ['error' => $exception->getMessage()]);
    }
}
