<?php

namespace App\Livewire;

use App\Enums\CostMetric;
use App\Enums\Job;
use App\Enums\RevenueMetric;
use App\Models\Server;
use App\Services\MarketAnalyzerService;
use App\Support\MarketFilterDefaults;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MarketAnalyzer extends Component
{
    public ?int   $serverId   = null;
    public ?int   $jobId      = null;
    public int    $minLevel   = MarketFilterDefaults::MIN_LEVEL;
    public int    $maxLevel   = MarketFilterDefaults::MAX_LEVEL;
    public int    $minProfit  = MarketFilterDefaults::MIN_PROFIT;
    public float  $minMargin  = MarketFilterDefaults::MIN_MARGIN;
    public float  $minSales        = MarketFilterDefaults::MIN_SALES;
    public bool   $gatherableOnly  = false;
    public string $costMetric      = 'min_listing';
    public string $revMetric       = 'home_min_listing';

    // Modo de análise: 'craft' | 'gathering' | 'all'
    public string  $analysisMode     = 'craft';
    public ?string $gatheringSource  = null;   // null | 'gathering' | 'fishing'

    public bool    $analyzed = false;
    public ?string $error    = null;

    public int    $currentPage   = 1;
    public int    $perPage       = 20;
    public int    $totalResults  = 0;
    public string $sortColumn    = 'profit';
    public string $sortDirection = 'desc';

    // Character — populated once at mount, stable for the session
    public bool    $characterVerified  = false;
    public ?string $characterName      = null;
    public ?string $characterServer    = null;
    public ?string $characterAvatar    = null;
    public array   $characterJobLevels = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && $user->isCharacterVerified()) {
            $this->characterVerified  = true;
            $this->characterName      = $user->character_name;
            $this->characterServer    = $user->character_server;
            $this->characterAvatar    = $user->character_avatar;
            $this->characterJobLevels = $user->job_levels ?? [];
        }
    }

    // Auto-fill level range whenever the selected job changes (requires verified character)
    public function updatedJobId(): void
    {
        if (!$this->characterVerified || !$this->jobId || $this->analysisMode === 'gathering') {
            return;
        }
        $level = $this->characterJobLevels[$this->jobId] ?? 0;
        if ($level > 0) {
            $this->maxLevel = $level;
            $this->minLevel = max(1, $level - 10);
        }
    }

    public function setAnalysisMode(string $mode): void
    {
        $this->analysisMode = $mode;
        // Limpa job ao entrar no modo coleta puro
        if ($mode === 'gathering') {
            $this->jobId = null;
        }
    }

    public function toggleGatheringSource(?string $source): void
    {
        $this->gatheringSource = ($this->gatheringSource === $source) ? null : $source;
    }

    // Character banner button: set server + level (if job selected)
    public function applyCharacterFilter(): void
    {
        if (!$this->characterVerified) {
            return;
        }

        $server = Server::where('name', $this->characterServer)->first();
        if ($server) {
            $this->serverId = $server->id;
        }

        if ($this->jobId) {
            $level = $this->characterJobLevels[$this->jobId] ?? 0;
            if ($level > 0) {
                $this->maxLevel = $level;
                $this->minLevel = max(1, $level - 10);
            }
        }
    }

    public function runAnalysis(MarketAnalyzerService $service): void
    {
        $this->validate(['serverId' => 'required|exists:servers,id']);

        $this->error    = null;
        $this->analyzed = false;

        try {
            $server = Server::findOrFail($this->serverId);

            $filters = array_filter([
                'cost_metric'      => $this->costMetric,
                'revenue_metric'   => $this->revMetric,
                'job_id'           => $this->analysisMode !== 'gathering' ? $this->jobId : null,
                'min_level'        => $this->minLevel ?: null,
                'max_level'        => $this->maxLevel ?: null,
                'min_profit'       => $this->minProfit,
                'min_margin'       => $this->analysisMode !== 'gathering' ? $this->minMargin : null,
                'min_sales'        => $this->minSales,
                'gatherable_only'  => ($this->analysisMode !== 'gathering' && $this->gatherableOnly) ?: null,
                'gathering_source' => $this->gatheringSource,
            ]);

            $rawResults = match ($this->analysisMode) {
                'gathering' => $service->analyzeGathering($server->slug, $filters),
                'all'       => array_merge(
                                   $service->analyze($server->slug, $filters),
                                   $service->analyzeGathering($server->slug, $filters)
                               ),
                default     => $service->analyze($server->slug, $filters),
            };

            // Modo "all": reordena o merge por lucro
            if ($this->analysisMode === 'all') {
                usort($rawResults, fn($a, $b) => $b->profit <=> $a->profit);
            }

            $serialized = array_map(fn($r) => $r->jsonSerialize(), $rawResults);
            $this->storeResults($serialized);
            $this->totalResults = count($serialized);
            $this->analyzed     = true;
            $this->currentPage  = 1;

            $service->saveAnalysis($server, $filters, $rawResults, 0);

            $this->dispatch('results-updated', [
                'labels' => array_column(array_slice($serialized, 0, 10), 'itemName'),
                'data'   => array_column(array_slice($serialized, 0, 10), 'profit'),
            ]);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Resultados da análise ficam em cache (não em propriedade pública) para
     * não inflar o payload Livewire re-serializado a cada interação (sort,
     * paginação, filtro) — pode chegar a 1000 itens por análise.
     */
    private function storeResults(array $results): void
    {
        Cache::put($this->resultsCacheKey(), $results, now()->addHour());
    }

    private function getResults(): array
    {
        return Cache::get($this->resultsCacheKey(), []);
    }

    private function resultsCacheKey(): string
    {
        return 'livewire:market-analyzer:' . $this->getId() . ':results';
    }

    public function getPagedResultsProperty(): LengthAwarePaginator
    {
        $sorted = $this->getResults();

        usort($sorted, function (array $a, array $b) {
            $va = $a[$this->sortColumn] ?? '';
            $vb = $b[$this->sortColumn] ?? '';

            $cmp = is_string($va)
                ? strcmp($va, $vb)
                : ($va <=> $vb);

            return $this->sortDirection === 'asc' ? $cmp : -$cmp;
        });

        return new LengthAwarePaginator(
            array_slice($sorted, ($this->currentPage - 1) * $this->perPage, $this->perPage),
            count($sorted),
            $this->perPage,
            $this->currentPage,
        );
    }

    public function sortBy(string $column): void
    {
        $allowed = ['itemName', 'profit', 'costEstimate', 'revenueEstimate', 'marginPercent', 'salesPerWeek'];
        if (!in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = 'desc';
        }
        $this->currentPage = 1;
    }

    public function nextPage(): void
    {
        if ($this->pagedResults->hasMorePages()) {
            $this->currentPage++;
        }
    }

    public function prevPage(): void
    {
        if (!$this->pagedResults->onFirstPage()) {
            $this->currentPage--;
        }
    }

    public function render()
    {
        return view('livewire.market-analyzer', [
            'servers'        => Server::active()->orderBy('region')->orderBy('name')->get(),
            'jobs'           => Job::cases(),
            'costMetrics'    => CostMetric::cases(),
            'revMetrics'     => RevenueMetric::cases(),
            'results'        => $this->getResults(),
            'pagedResults'   => $this->pagedResults,
            'sortColumn'     => $this->sortColumn,
            'sortDirection'  => $this->sortDirection,
        ]);
    }
}
