<?php

namespace App\Console\Commands;

use App\Clients\XIVApiClient;
use Illuminate\Console\Command;

class MarketSyncItemsCommand extends Command
{
    protected $signature   = 'market:sync:items
                              {--page=1 : Starting page}
                              {--all : Sync all pages}
                              {--limit=100 : Items per page}';
    protected $description = 'Sync craftable items and recipes from XIVAPI';

    public function handle(XIVApiClient $client): int
    {
        $page  = (int) $this->option('page');
        $limit = (int) $this->option('limit');
        $all   = $this->option('all');

        $this->info("Syncing recipes from XIVAPI (page {$page}, limit {$limit})...");

        do {
            $data    = $client->getAllRecipes($limit, $page);
            $recipes = $data['results'];
            $pages   = $data['pages'];

            if (empty($recipes)) {
                $this->warn("No recipes found on page {$page}.");
                break;
            }

            $bar = $this->output->createProgressBar(count($recipes));
            $bar->start();

            foreach ($recipes as $summary) {
                try {
                    // List endpoint returns summaries only; fetch full recipe data individually
                    $recipeData = $client->getRecipeById((int) $summary['ID']);
                    if ($recipeData) {
                        $recipe = $client->syncRecipeToDatabase($recipeData);
                        if ($recipe) {
                            $client->syncRecipeLookupToDatabase($recipe->item_id);
                        }
                    }
                } catch (\Throwable $e) {
                    $this->warn("\nFailed to sync recipe #{$summary['ID']}: " . $e->getMessage());
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Page {$page}/{$pages} synced (" . count($recipes) . " recipes).");

            $page++;
        } while ($all && $page <= $pages);

        $this->info('Sync complete!');

        return self::SUCCESS;
    }
}
