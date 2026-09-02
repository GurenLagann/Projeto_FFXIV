<?php

namespace App\Jobs;

use App\Models\Analysis;
use App\Services\MarketAnalyzerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchMarketDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly string $serverSlug,
        public readonly array  $filters,
        public readonly int    $analysisId,
    ) {}

    public function handle(MarketAnalyzerService $service): void
    {
        $start   = microtime(true);
        $results = $service->analyze($this->serverSlug, $this->filters);
        $elapsed = (int) ((microtime(true) - $start) * 1000);

        $analysis = Analysis::find($this->analysisId);
        if ($analysis) {
            $analysis->update([
                'results'             => array_map(fn($r) => $r->jsonSerialize(), $results),
                'execution_time'      => $elapsed,
                'total_opportunities' => count(array_filter($results, fn($r) => $r->isProfitable)),
                'completed_at'        => now(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('FetchMarketDataJob failed', [
            'analysisId' => $this->analysisId,
            'server'     => $this->serverSlug,
            'error'      => $exception->getMessage(),
        ]);

        Analysis::find($this->analysisId)?->update([
            'failed_at' => now(),
            'error'     => $exception->getMessage(),
        ]);
    }
}
