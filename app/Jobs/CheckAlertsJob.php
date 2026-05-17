<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Services\MarketAnalyzerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckAlertsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(MarketAnalyzerService $service): void
    {
        $alerts = Alert::where('is_active', true)
            ->with(['item', 'user', 'server'])
            ->get();

        foreach ($alerts as $alert) {
            $prices = $service->getCurrentPrices($alert->server->slug, [$alert->item_id]);
            $price  = $prices[$alert->item_id] ?? null;

            if (!$price) {
                continue;
            }

            if ($price->minPriceNQ > 0 && $price->minPriceNQ <= $alert->min_profit) {
                $alert->update(['last_notified_at' => now()]);
            }
        }
    }
}
