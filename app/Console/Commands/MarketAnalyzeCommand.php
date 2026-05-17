<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\MarketAnalyzerService;
use Illuminate\Console\Command;

class MarketAnalyzeCommand extends Command
{
    protected $signature = 'market:analyze
                            {server? : World server name (e.g. Balmung)}
                            {--job= : Job ID filter (8=CRP, 9=BSM, ...)}
                            {--min-level=1 : Minimum craft level}
                            {--max-level=100 : Maximum craft level}
                            {--min-profit=5000 : Minimum profit in gil}
                            {--min-margin=20 : Minimum profit margin %}
                            {--min-sales=10 : Minimum sales per week}
                            {--top=20 : Number of results to show}
                            {--json : Output as JSON}';

    protected $description = 'Analyze FFXIV market profitability for a server';

    public function handle(MarketAnalyzerService $service): int
    {
        $server = $this->argument('server') ?? config('market.analysis.default_server', 'Balmung');

        $filters = [
            'job_id'     => $this->option('job') ? (int) $this->option('job') : null,
            'min_level'  => (int) $this->option('min-level'),
            'max_level'  => (int) $this->option('max-level'),
            'min_profit' => (int) $this->option('min-profit'),
            'min_margin' => (float) $this->option('min-margin'),
            'min_sales'  => (float) $this->option('min-sales'),
            'limit'      => (int) $this->option('top'),
        ];

        $this->info("Analyzing market for server: {$server}");

        $serverModel = Server::whereRaw('LOWER(name) = ?', [strtolower($server)])->first();

        try {
            $start   = microtime(true);
            $results = $service->analyze($server, $filters);
            $elapsed = (int) ((microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->error("Analysis failed: " . $e->getMessage());
            return self::FAILURE;
        }

        if ($serverModel) {
            $analysis = $service->saveAnalysis($serverModel, $filters, $results, $elapsed);
            $this->line("Analysis #{$analysis->id} saved.");
        }

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        if (empty($results)) {
            $this->warn('No profitable opportunities found with the given filters.');
            return self::SUCCESS;
        }

        $rows = array_map(fn($r) => [
            $r->itemName,
            number_format($r->profit) . ' gil',
            number_format($r->costEstimate) . ' gil',
            number_format($r->revenueEstimate) . ' gil',
            number_format($r->marginPercent, 1) . '%',
            number_format($r->salesPerWeek, 1) . '/wk',
        ], $results);

        $this->table(
            ['Item', 'Profit', 'Cost', 'Revenue', 'Margin', 'Sales/Wk'],
            $rows
        );

        $this->info(count($results) . ' opportunities found.');

        return self::SUCCESS;
    }
}
