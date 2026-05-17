<?php

namespace App\Http\Controllers\Api;

use App\Clients\XIVApiClient;
use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\Server;
use App\Services\MarketAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function prices(Request $request, MarketAnalyzerService $analyzer, string $server, string $itemIds): JsonResponse
    {
        $ids    = explode(',', $itemIds);
        $prices = $analyzer->getCurrentPrices($server, $ids);
        return response()->json(array_map(fn($p) => (array) $p, $prices));
    }

    public function analyze(Request $request, MarketAnalyzerService $analyzer): JsonResponse
    {
        $request->validate([
            'server'         => 'required|string|exists:servers,slug',
            'cost_metric'    => 'nullable|string',
            'revenue_metric' => 'nullable|string',
            'job_id'         => 'nullable|integer',
            'min_level'      => 'nullable|integer|min:1|max:100',
            'max_level'      => 'nullable|integer|min:1|max:100',
            'min_profit'     => 'nullable|integer|min:0',
            'min_margin'     => 'nullable|numeric|min:0|max:100',
            'min_sales'      => 'nullable|numeric|min:0',
        ]);

        $results = $analyzer->analyze($request->server, $request->only([
            'cost_metric', 'revenue_metric', 'job_id',
            'min_level', 'max_level', 'min_profit', 'min_margin', 'min_sales',
        ]));

        return response()->json($results);
    }

    public function opportunities(string $server): JsonResponse
    {
        $serverModel = Server::where('slug', $server)->firstOrFail();
        $analysis    = Analysis::where('server_id', $serverModel->id)
            ->whereNotNull('completed_at')
            ->latest()
            ->first();

        if (!$analysis) {
            return response()->json([]);
        }

        return response()->json($analysis->results ?? []);
    }

    public function searchItems(Request $request, XIVApiClient $xivapi): JsonResponse
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $xivapi->searchItems($query);
        return response()->json($results);
    }

    public function servers(): JsonResponse
    {
        return response()->json(
            Server::active()->orderBy('region')->orderBy('datacenter')->orderBy('name')->get()
        );
    }
}
