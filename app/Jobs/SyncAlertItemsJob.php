<?php

namespace App\Jobs;

use App\Clients\XIVApiClient;
use App\Models\Alert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAlertItemsJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 1;
    public int $timeout = 600;

    public function __construct()
    {
        $this->onQueue('sync');
    }

    public function handle(XIVApiClient $client): void
    {
        $items = Alert::where('is_active', true)
            ->with('item.recipes')
            ->get()
            ->pluck('item')
            ->filter()
            ->unique('id');

        foreach ($items as $item) {
            foreach ($item->recipes as $recipe) {
                $recipeData = $client->getRecipeById($recipe->id);
                if (!$recipeData) {
                    continue;
                }

                $synced = $client->syncRecipeToDatabase($recipeData);
                if ($synced) {
                    $client->syncRecipeLookupToDatabase($synced->item_id);
                }
            }
        }
    }
}
