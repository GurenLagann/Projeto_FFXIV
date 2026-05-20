<?php

namespace App\Jobs;

use App\Clients\XIVApiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncRecipePageJob implements ShouldQueue
{
    use Queueable;

    public int   $tries   = 3;
    public int   $timeout = 300;
    public array $backoff = [60, 120, 300];

    public function __construct(
        public readonly int $page,
        public readonly int $limit,
    ) {
        $this->onQueue('sync');
    }

    public function handle(XIVApiClient $client): void
    {
        $data    = $client->getAllRecipes($this->limit, $this->page);
        $recipes = $data['results'];

        foreach ($recipes as $summary) {
            $recipeData = $client->getRecipeById((int) $summary['ID']);
            if (!$recipeData) {
                continue;
            }

            $recipe = $client->syncRecipeToDatabase($recipeData);
            if ($recipe) {
                $client->syncRecipeLookupToDatabase($recipe->item_id);
            }
        }
    }
}
