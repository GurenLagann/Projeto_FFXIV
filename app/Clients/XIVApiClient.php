<?php

namespace App\Clients;

use App\Models\GatheringItem;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\RecipeLookup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XIVApiClient
{
    private string $baseUrl;
    private string $v2BaseUrl;
    private int    $cacheTtl;

    private array $defaultHeaders = ['User-Agent' => 'FFXIV-MarketAnalyzer/1.0'];

    public function __construct()
    {
        $this->baseUrl   = config('market.api.xivapi.base_url', 'https://xivapi.com');
        $this->v2BaseUrl = config('market.api.xivapi.v2_base_url', 'https://v2.xivapi.com');
        $this->cacheTtl  = config('market.api.xivapi.cache_ttl', 86400);
    }

    private function cachedRequest(string $endpoint): array
    {
        $cacheKey = 'xivapi:' . md5($endpoint);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($endpoint) {
            return Http::timeout(15)
                ->withHeaders($this->defaultHeaders)
                ->retry(3, 100)
                ->get($this->baseUrl . $endpoint)
                ->throw()
                ->json();
        });
    }

    private function v2Get(string $endpoint): array
    {
        return Http::timeout(15)
            ->withHeaders($this->defaultHeaders)
            ->retry(3, 150)
            ->get($this->v2BaseUrl . $endpoint)
            ->throw()
            ->json();
    }

    private function cachedV2Get(string $endpoint): array
    {
        $cacheKey = 'xivapi_v2:' . md5($endpoint);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($endpoint) {
            return $this->v2Get($endpoint);
        });
    }

    /**
     * Returns null on API/network failure, empty array when genuinely no results found.
     */
    public function searchCharacters(string $name, string $server): ?array
    {
        try {
            $name   = urlencode($name);
            $server = urlencode($server);
            $data   = $this->cachedRequest("/character/search?name={$name}&server={$server}");
            return $data['Results'] ?? [];
        } catch (\Throwable) {
            return null;
        }
    }

    public function getCharacter(int $lodestoneId): ?array
    {
        try {
            return $this->cachedRequest("/character/{$lodestoneId}?data=CJ");
        } catch (\Throwable) {
            return null;
        }
    }

    public function getItem(int $itemId): array
    {
        return $this->cachedRequest("/item/{$itemId}");
    }

    public function searchItems(string $name): array
    {
        $name = urlencode($name);
        $data = $this->cachedRequest("/search?string={$name}&indexes=Item&columns=ID,Name,Icon,LevelItem");
        return $data['Results'] ?? [];
    }

    public function getRecipeByItemId(int $itemId): ?array
    {
        try {
            $data    = $this->cachedRequest("/recipe?limit=10&filters=ItemResult.ID={$itemId}");
            $results = $data['Results'] ?? [];
            return empty($results) ? null : $results[0];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Retorna todas as receitas (summaries) de um item.
     * Um item pode ter múltiplas receitas (e.g., versões de diferentes jobs).
     */
    public function getRecipesByItemId(int $itemId): array
    {
        try {
            $data = $this->cachedRequest("/recipe?limit=10&filters=ItemResult.ID={$itemId}");
            return $data['Results'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Sincroniza dados de coleta (gathering/fishing) apenas para os item_ids fornecidos.
     * Pagina as sheets até encontrar todos os IDs ou esgotar os dados, evitando o sync total.
     *
     * @param  array<int|string> $itemIds
     */
    public function syncGatheringForItems(array $itemIds): int
    {
        if (empty($itemIds)) {
            return 0;
        }

        $needed  = array_flip(array_map('strval', $itemIds));
        $synced  = 0;
        $limit   = 500;

        foreach (['gathering', 'fishing'] as $source) {
            $after     = 0;
            $remaining = $needed;

            do {
                try {
                    $rows = $source === 'gathering'
                        ? $this->getGatheringPage($after, $limit)
                        : $this->getFishingPage($after, $limit);
                } catch (\Throwable) {
                    break;
                }

                if (empty($rows)) {
                    break;
                }

                // Filtra apenas as linhas relevantes antes de chamar o upsert
                $relevant = array_filter($rows, function (array $row) use ($remaining) {
                    $itemId = (string) ($row['fields']['Item']['value'] ?? 0);
                    return isset($remaining[$itemId]);
                });

                if (!empty($relevant)) {
                    $synced += $this->syncGatheringRows(array_values($relevant), $source);

                    // Remove os itens já encontrados para poder parar cedo
                    foreach ($relevant as $row) {
                        $itemId = (string) ($row['fields']['Item']['value'] ?? 0);
                        unset($remaining[$itemId]);
                    }
                }

                $after = (int) end($rows)['row_id'];

            } while (count($rows) >= $limit && !empty($remaining));
        }

        return $synced;
    }

    public function getRecipeById(int $recipeId): ?array
    {
        try {
            return $this->cachedRequest("/recipe/{$recipeId}");
        } catch (\Throwable) {
            return null;
        }
    }

    public function getAllRecipes(int $limit = 100, int $page = 1): array
    {
        try {
            $data = $this->cachedRequest("/recipe?limit={$limit}&page={$page}");
            return [
                'results' => $data['Results'] ?? [],
                'total'   => $data['Pagination']['ResultsTotal'] ?? 0,
                'pages'   => $data['Pagination']['PageTotal'] ?? 1,
            ];
        } catch (\Throwable) {
            return ['results' => [], 'total' => 0, 'pages' => 1];
        }
    }

    public function syncRecipeToDatabase(array $recipeData): ?Recipe
    {
        $itemResult = $recipeData['ItemResult'] ?? null;
        if (!$itemResult) {
            return null;
        }

        $item = Item::updateOrCreate(
            ['id' => (string) $itemResult['ID']],
            [
                'name'         => $itemResult['Name'] ?? 'Unknown',
                'level'        => $itemResult['LevelItem'] ?? 0,
                'icon'         => $itemResult['Icon'] ?? null,
                'is_craftable' => true,
            ]
        );

        $recipe = Recipe::updateOrCreate(
            ['id' => $recipeData['ID']],
            [
                'item_id'     => $item->id,
                'job_id'      => $recipeData['ClassJob']['ID'] ?? 0,
                'craft_level' => $recipeData['RecipeLevelTable']['ClassJobLevel'] ?? 1,
                'yields'      => $recipeData['AmountResult'] ?? 1,
                'can_be_hq'   => (bool) ($recipeData['CanHq'] ?? false),
                'stars'       => $recipeData['RecipeLevelTable']['Stars'] ?? 0,
                'difficulty'  => $recipeData['RecipeLevelTable']['Difficulty'] ?? 0,
            ]
        );

        for ($i = 0; $i <= 8; $i++) {
            $ingKey = "ItemIngredient{$i}";
            $qtyKey = "AmountIngredient{$i}";

            $ingredient = $recipeData[$ingKey] ?? null;
            $qty        = $recipeData[$qtyKey] ?? 0;

            if (!$ingredient || !$qty || empty($ingredient['ID'])) {
                continue;
            }

            $matItem = Item::updateOrCreate(
                ['id' => (string) $ingredient['ID']],
                [
                    'name'  => $ingredient['Name'] ?? 'Unknown',
                    'level' => $ingredient['LevelItem'] ?? 0,
                    'icon'  => $ingredient['Icon'] ?? null,
                ]
            );

            \App\Models\RecipeMaterial::updateOrCreate(
                ['recipe_id' => $recipe->id, 'material_id' => $matItem->id],
                ['quantity'  => $qty]
            );
        }

        return $recipe;
    }

    // ─── XIVAPI v2 ────────────────────────────────────────────────────────────

    /**
     * Busca o RecipeLookup para um item e persiste no banco.
     * Chamado automaticamente durante o sync de receitas.
     */
    public function syncRecipeLookupToDatabase(string $itemId): void
    {
        try {
            $data   = $this->cachedV2Get("/api/sheet/RecipeLookup/{$itemId}?fields=CRP,BSM,ARM,GSM,LTW,WVR,ALC,CUL");
            $fields = $data['fields'] ?? [];

            RecipeLookup::updateOrCreate(
                ['item_id' => $itemId],
                [
                    'crp_id' => ($fields['CRP']['value'] ?? 0) ?: null,
                    'bsm_id' => ($fields['BSM']['value'] ?? 0) ?: null,
                    'arm_id' => ($fields['ARM']['value'] ?? 0) ?: null,
                    'gsm_id' => ($fields['GSM']['value'] ?? 0) ?: null,
                    'ltw_id' => ($fields['LTW']['value'] ?? 0) ?: null,
                    'wvr_id' => ($fields['WVR']['value'] ?? 0) ?: null,
                    'alc_id' => ($fields['ALC']['value'] ?? 0) ?: null,
                    'cul_id' => ($fields['CUL']['value'] ?? 0) ?: null,
                ]
            );
        } catch (\Throwable $e) {
            Log::debug("RecipeLookup skip item={$itemId}: " . $e->getMessage());
        }
    }

    /**
     * Retorna uma página da sheet GatheringItem (MIN/BTN).
     * Paginar incrementando $after com o último row_id retornado.
     */
    public function getGatheringPage(int $after = 0, int $limit = 500): array
    {
        $data = $this->v2Get(
            "/api/sheet/GatheringItem?fields=Item,GatheringItemLevel.GatheringItemLevel,GatheringItemLevel.Stars,IsHidden&limit={$limit}&after={$after}"
        );
        return $data['rows'] ?? [];
    }

    /**
     * Retorna uma página da sheet SpearfishingItem (FSH).
     */
    public function getFishingPage(int $after = 0, int $limit = 500): array
    {
        $data = $this->v2Get(
            "/api/sheet/SpearfishingItem?fields=Item,GatheringItemLevel.GatheringItemLevel&limit={$limit}&after={$after}"
        );
        return $data['rows'] ?? [];
    }

    /**
     * Persiste um lote de rows da GatheringItem sheet.
     * Só insere para item_ids que já existem na tabela items.
     *
     * @param  array  $rows     Rows vindas de getGatheringPage() ou getFishingPage()
     * @param  string $source   'gathering' | 'fishing'
     * @return int              Número de registros upsertados
     */
    public function syncGatheringRows(array $rows, string $source): int
    {
        if (empty($rows)) {
            return 0;
        }

        $records = [];

        foreach ($rows as $row) {
            $fields  = $row['fields'] ?? [];
            $itemId  = (string) ($fields['Item']['value'] ?? 0);
            $isHidden = (bool) ($fields['IsHidden'] ?? false);

            if (!$itemId || $itemId === '0' || $isHidden) {
                continue;
            }

            $level = $fields['GatheringItemLevel']['fields']['GatheringItemLevel'] ?? 0;
            $stars = $fields['GatheringItemLevel']['fields']['Stars'] ?? 0;

            $records[$itemId] = [
                'item_id'         => $itemId,
                'gathering_level' => (int) $level,
                'stars'           => (int) $stars,
                'source'          => $source,
            ];
        }

        if (empty($records)) {
            return 0;
        }

        // Filtra apenas item_ids que existem na tabela items
        $existingIds = Item::whereIn('id', array_keys($records))->pluck('id')->all();
        $filtered    = array_filter($records, fn($r) => in_array($r['item_id'], $existingIds));

        if (empty($filtered)) {
            return 0;
        }

        GatheringItem::upsert(
            array_values($filtered),
            ['item_id'],
            ['gathering_level', 'stars', 'source', 'updated_at']
        );

        return count($filtered);
    }
}
