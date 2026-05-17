<?php

namespace App\Clients;

use App\DTOs\ItemPrice;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UniversalisClient
{
    private string $baseUrl;
    private int    $cacheTtl;

    // Universalis accepts up to 100 IDs per request
    private const CHUNK_SIZE = 100;

    public function __construct()
    {
        $this->baseUrl  = config('market.api.universalis.base_url', 'https://universalis.app/api/v2');
        $this->cacheTtl = config('market.api.universalis.cache_ttl', 300);
    }

    /**
     * Fetch prices for many items using per-item cache + parallel HTTP requests.
     * Cache stores raw Universalis payload per item, so any future analysis on
     * the same server reuses individual items regardless of which chunk they were in.
     */
    public function getPrices(string $server, array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        $results  = [];
        $uncached = [];

        // 1. Warm results from per-item cache
        foreach ($itemIds as $id) {
            $raw = Cache::get("univ:item:{$server}:{$id}");
            if ($raw !== null) {
                $results[$id] = ItemPrice::fromUniversalisResponse($raw, (int) $id);
            } else {
                $uncached[] = $id;
            }
        }

        if (empty($uncached)) {
            return $results;
        }

        // 2. Fire all chunks in parallel via Http::pool()
        $chunks = array_chunk($uncached, self::CHUNK_SIZE);

        $responses = Http::pool(function (Pool $pool) use ($server, $chunks) {
            return array_map(
                fn($chunk) => $pool
                    ->withHeaders(['User-Agent' => 'FFXIV-MarketAnalyzer/1.0'])
                    ->timeout(20)
                    ->get("{$this->baseUrl}/{$server}/" . implode(',', $chunk)),
                $chunks
            );
        });

        // 3. Process responses, populate cache per item
        foreach ($chunks as $i => $chunk) {
            try {
                $response = $responses[$i];
                if (!($response instanceof \Illuminate\Http\Client\Response) || !$response->successful()) {
                    continue;
                }

                $data = $response->json();

                if (count($chunk) === 1) {
                    $itemId = $chunk[0];
                    Cache::put("univ:item:{$server}:{$itemId}", $data, $this->cacheTtl);
                    $results[$itemId] = ItemPrice::fromUniversalisResponse($data, (int) $itemId);
                    continue;
                }

                foreach ($data['items'] ?? [] as $itemId => $itemData) {
                    Cache::put("univ:item:{$server}:{$itemId}", $itemData, $this->cacheTtl);
                    $results[$itemId] = ItemPrice::fromUniversalisResponse($itemData, (int) $itemId);
                }
            } catch (\Throwable) {
                // skip failed chunk
            }
        }

        return $results;
    }

    public function getPrice(string $server, int $itemId): ?ItemPrice
    {
        try {
            $raw = Cache::get("univ:item:{$server}:{$itemId}");
            if ($raw !== null) {
                return ItemPrice::fromUniversalisResponse($raw, $itemId);
            }

            $data = Http::withHeaders(['User-Agent' => 'FFXIV-MarketAnalyzer/1.0'])
                ->timeout(10)
                ->retry(3, 200)
                ->get("{$this->baseUrl}/{$server}/{$itemId}")
                ->throw()
                ->json();

            Cache::put("univ:item:{$server}:{$itemId}", $data, $this->cacheTtl);
            return ItemPrice::fromUniversalisResponse($data, $itemId);
        } catch (\Throwable) {
            return null;
        }
    }
}
