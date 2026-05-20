<?php

namespace App\Console\Commands;

use App\Clients\XIVApiClient;
use App\Jobs\SyncRecipePageJob;
use Illuminate\Console\Command;

class MarketSyncItemsCommand extends Command
{
    protected $signature   = 'market:sync:items
                              {--page=1 : Starting page}
                              {--all : Sync all pages}
                              {--limit=100 : Items per page}
                              {--queue : Despacha um job por página na fila "sync" em vez de rodar inline}';
    protected $description = 'Sync craftable items and recipes from XIVAPI';

    public function handle(XIVApiClient $client): int
    {
        $page  = (int) $this->option('page');
        $limit = (int) $this->option('limit');
        $all   = $this->option('all');
        $queue = $this->option('queue');

        if ($queue) {
            return $this->dispatchToQueue($client, $page, $limit, $all);
        }

        return $this->runInline($client, $page, $limit, $all);
    }

    private function dispatchToQueue(XIVApiClient $client, int $startPage, int $limit, bool $all): int
    {
        $this->info("Buscando total de páginas (limit={$limit})...");

        $data  = $client->getAllRecipes($limit, $startPage);
        $pages = $all ? $data['pages'] : $startPage;

        if ($data['total'] === 0) {
            $this->warn('Nenhuma receita encontrada na XIVAPI.');
            return self::FAILURE;
        }

        $dispatched = 0;
        for ($p = $startPage; $p <= $pages; $p++) {
            SyncRecipePageJob::dispatch($p, $limit);
            $dispatched++;
        }

        $this->info("{$dispatched} job(s) despachados para a fila \"sync\".");
        $this->line('  Execute: php artisan queue:work redis --queue=sync,default --timeout=300');

        return self::SUCCESS;
    }

    private function runInline(XIVApiClient $client, int $page, int $limit, bool $all): int
    {
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
