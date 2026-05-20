<?php

namespace App\Console\Commands;

use App\Clients\XIVApiClient;
use App\Models\Item;
use Illuminate\Console\Command;

class MarketSyncItemCommand extends Command
{
    protected $signature = 'market:sync:item
                            {item : ID numérico do item ou termo de busca por nome}
                            {--with-gathering : Sincroniza dados de coleta para os materiais}';

    protected $description = 'Sincroniza receita(s) de um item específico sem fazer o sync total';

    public function handle(XIVApiClient $client): int
    {
        $arg = $this->argument('item');

        $item = $this->resolveItem($arg);
        if ($item === null) {
            return self::FAILURE;
        }

        $item->load('recipes');

        if ($item->recipes->isEmpty()) {
            $this->warn("Item \"{$item->name}\" (#{$item->id}) não possui receitas no banco local.");
            $this->line('  Execute market:sync:items para importar receitas novas da XIVAPI.');
            return self::FAILURE;
        }

        $this->info("{$item->recipes->count()} receita(s) encontrada(s) para \"{$item->name}\" (#{$item->id}).");

        $materialIds = [];
        $syncedCount = 0;

        foreach ($item->recipes as $localRecipe) {
            $recipeId = $localRecipe->id;
            $this->line("  Buscando receita #{$recipeId} na XIVAPI...");

            $recipeData = $client->getRecipeById($recipeId);
            if (!$recipeData) {
                $this->warn("  Falha ao buscar receita #{$recipeId} — pulando.");
                continue;
            }

            $recipe = $client->syncRecipeToDatabase($recipeData);
            if (!$recipe) {
                $this->warn("  Falha ao salvar receita #{$recipeId} no banco — pulando.");
                continue;
            }

            $client->syncRecipeLookupToDatabase($recipe->item_id);

            // Coleta IDs dos materiais para uso posterior (gathering)
            for ($i = 0; $i <= 8; $i++) {
                $ingredient = $recipeData["ItemIngredient{$i}"] ?? null;
                $qty        = $recipeData["AmountIngredient{$i}"] ?? 0;
                if ($ingredient && $qty && !empty($ingredient['ID'])) {
                    $materialIds[] = (string) $ingredient['ID'];
                }
            }

            $syncedCount++;
            $itemName = $recipeData['ItemResult']['Name'] ?? "Item #{$item->id}";
            $job      = $recipeData['ClassJob']['Abbreviation'] ?? '?';
            $level    = $recipeData['RecipeLevelTable']['ClassJobLevel'] ?? '?';
            $this->info("  ✔ {$itemName} — {$job} Lv{$level} (recipe #{$recipeId})");
        }

        if ($syncedCount === 0) {
            $this->error('Nenhuma receita foi sincronizada.');
            return self::FAILURE;
        }

        $this->line('');

        if ($this->option('with-gathering') && !empty($materialIds)) {
            $materialIds = array_unique($materialIds);
            $this->info('Sincronizando dados de coleta para ' . count($materialIds) . ' material(is)...');
            $gatheredCount = $client->syncGatheringForItems($materialIds);
            $this->info("  → {$gatheredCount} item(ns) de coleta sincronizados.");
        } elseif (!empty($materialIds)) {
            $this->line('<fg=gray>  Dica: use --with-gathering para sincronizar dados de coleta dos materiais.</>');
        }

        $this->info("{$syncedCount} receita(s) sincronizada(s) com sucesso.");

        return self::SUCCESS;
    }

    private function resolveItem(string $arg): ?Item
    {
        // Argumento numérico: busca diretamente pelo ID no banco
        if (ctype_digit($arg)) {
            $item = Item::find($arg);
            if (!$item) {
                $this->error("Item ID #{$arg} não encontrado no banco local.");
                $this->line('  Execute market:sync:items para importar receitas da XIVAPI.');
                return null;
            }
            return $item;
        }

        // Argumento textual: busca por nome no banco local
        $this->line("Buscando \"{$arg}\" no banco local...");

        $items = Item::where('name', 'like', "%{$arg}%")
            ->orderByRaw('name = ? desc', [$arg]) // match exato primeiro
            ->limit(10)
            ->get(['id', 'name']);

        if ($items->isEmpty()) {
            $this->error("Nenhum item encontrado para \"{$arg}\".");
            $this->line('  Execute market:sync:items para importar mais itens, ou use o ID numérico diretamente.');
            return null;
        }

        if ($items->count() === 1) {
            $item = $items->first();
            $this->line("  Encontrado: {$item->name} (ID #{$item->id})");
            return $item;
        }

        // Múltiplos resultados: exibe menu de escolha
        $choices = $items->map(fn($r) => "[#{$r->id}] {$r->name}")->all();

        $choice = $this->choice(
            'Múltiplos itens encontrados. Selecione um:',
            $choices,
            0
        );

        preg_match('/\[#(\d+)\]/', $choice, $matches);

        if (empty($matches[1])) {
            $this->error('Não foi possível determinar o ID do item selecionado.');
            return null;
        }

        return Item::find((int) $matches[1]);
    }
}
