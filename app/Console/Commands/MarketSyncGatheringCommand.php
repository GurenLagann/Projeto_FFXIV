<?php

namespace App\Console\Commands;

use App\Clients\XIVApiClient;
use Illuminate\Console\Command;

class MarketSyncGatheringCommand extends Command
{
    protected $signature   = 'market:sync:gathering
                              {--limit=500 : Rows por página da API}
                              {--no-fish   : Pula SpearfishingItem}';
    protected $description = 'Sync itens coletáveis (MIN/BTN/FSH) do XIVAPI v2';

    public function handle(XIVApiClient $client): int
    {
        $limit  = (int) $this->option('limit');
        $noFish = $this->option('no-fish');

        $this->info('Syncing GatheringItem (MIN/BTN)...');
        $totalGathering = $this->syncSheet($client, 'gathering', $limit);
        $this->info("  → {$totalGathering} itens coletáveis sincronizados.");

        if (!$noFish) {
            $this->info('Syncing SpearfishingItem (FSH)...');
            $totalFishing = $this->syncSheet($client, 'fishing', $limit);
            $this->info("  → {$totalFishing} itens de pesca sincronizados.");
        }

        $this->info('Sync de gathering completo!');

        return self::SUCCESS;
    }

    private function syncSheet(XIVApiClient $client, string $source, int $limit): int
    {
        $after = 0;
        $total = 0;

        do {
            try {
                $rows = $source === 'gathering'
                    ? $client->getGatheringPage($after, $limit)
                    : $client->getFishingPage($after, $limit);
            } catch (\Throwable $e) {
                $this->error("Erro ao buscar página (after={$after}): " . $e->getMessage());
                break;
            }

            if (empty($rows)) {
                break;
            }

            $synced = $client->syncGatheringRows($rows, $source);
            $total += $synced;

            $lastRowId = (int) end($rows)['row_id'];
            $this->line("  after={$after} → {$synced} inseridos (last_row_id={$lastRowId})");

            $after = $lastRowId;

        } while (count($rows) >= $limit);

        return $total;
    }
}
