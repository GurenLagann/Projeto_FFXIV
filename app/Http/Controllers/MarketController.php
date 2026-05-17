<?php

namespace App\Http\Controllers;

use App\Enums\CostMetric;
use App\Enums\Job;
use App\Enums\RevenueMetric;
use App\Http\Requests\AnalyzeMarketRequest;
use App\Models\Analysis;
use App\Models\Server;
use App\Services\MarketAnalyzerService;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketController extends Controller
{
    public function index()
    {
        $servers        = Server::active()->orderBy('region')->orderBy('datacenter')->orderBy('name')->get();
        $jobs           = Job::cases();
        $costMetrics    = CostMetric::cases();
        $revMetrics     = RevenueMetric::cases();
        $recentAnalyses = Analysis::with('server')->latest()->take(10)->get();

        $chartLabels = collect([]);
        $chartData   = collect([]);

        if ($recentAnalyses->isNotEmpty()) {
            $latest = $recentAnalyses->first();
            if ($latest->results) {
                $topResults  = array_slice($latest->results, 0, 10);
                $chartLabels = collect($topResults)->pluck('itemName');
                $chartData   = collect($topResults)->pluck('profit');
            }
        }

        return view('market.dashboard', compact(
            'servers', 'jobs', 'costMetrics', 'revMetrics',
            'recentAnalyses', 'chartLabels', 'chartData'
        ));
    }

    public function analyze(AnalyzeMarketRequest $request, MarketAnalyzerService $analyzer)
    {
        $server  = Server::findOrFail($request->server_id);
        $filters = $request->toFilters();

        $start    = microtime(true);
        $results  = $analyzer->analyze($server->slug, $filters);
        $elapsed  = (int) ((microtime(true) - $start) * 1000);
        $analysis = $analyzer->saveAnalysis($server, $filters, $results, $elapsed);

        $serialized = array_map(fn($r) => $r->jsonSerialize(), $results);
        $perPage    = 20;
        $page       = max(1, (int) $request->get('page', 1));

        $paginator = new LengthAwarePaginator(
            array_slice($serialized, ($page - 1) * $perPage, $perPage),
            count($serialized),
            $perPage,
            $page,
            ['path' => route('market.analyze')]
        );

        return view('market.results', [
            'results'  => $paginator,
            'server'   => $server,
            'analysis' => $analysis,
            'filters'  => $filters,
            'elapsed'  => $elapsed,
        ]);
    }

    public function history()
    {
        $analyses = Analysis::with('server')->latest()->paginate(15);
        return view('market.history', compact('analyses'));
    }

    public function showAnalysis(Analysis $analysis)
    {
        $analysis->load('server');
        return view('market.show', compact('analysis'));
    }

    public function export(Analysis $analysis)
    {
        $results  = $analysis->results ?? [];
        $filename = "analysis-{$analysis->id}-{$analysis->server->slug}.csv";

        return response()->streamDownload(function () use ($results) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item', 'Profit (gil)', 'Cost (gil)', 'Revenue (gil)', 'Margin %', 'Sales/Week']);

            foreach ($results as $r) {
                fputcsv($handle, [
                    $r['itemName'],
                    $r['profit'],
                    $r['costEstimate'],
                    $r['revenueEstimate'],
                    round($r['marginPercent'], 2),
                    round($r['salesPerWeek'], 1),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
