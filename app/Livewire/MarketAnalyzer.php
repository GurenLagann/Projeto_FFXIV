<?php

namespace App\Livewire;

use App\Enums\CostMetric;
use App\Enums\Job;
use App\Enums\RevenueMetric;
use App\Models\Server;
use App\Services\MarketAnalyzerService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MarketAnalyzer extends Component
{
    public ?int   $serverId   = null;
    public ?int   $jobId      = null;
    public int    $minLevel   = 1;
    public int    $maxLevel   = 100;
    public int    $minProfit  = 5000;
    public float  $minMargin  = 20.0;
    public float  $minSales        = 5.0;
    public bool   $gatherableOnly  = false;
    public string $costMetric      = 'min_listing';
    public string $revMetric       = 'home_min_listing';

    public array   $results  = [];
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
        if (!$this->characterVerified || !$this->jobId) {
            return;
        }
        $level = $this->characterJobLevels[$this->jobId] ?? 0;
        if ($level > 0) {
            $this->maxLevel = $level;
            $this->minLevel = max(1, $level - 10);
        }
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
                'cost_metric'     => $this->costMetric,
                'revenue_metric'  => $this->revMetric,
                'job_id'          => $this->jobId,
                'min_level'       => $this->minLevel ?: null,
                'max_level'       => $this->maxLevel ?: null,
                'min_profit'      => $this->minProfit,
                'min_margin'      => $this->minMargin,
                'min_sales'       => $this->minSales,
                'gatherable_only' => $this->gatherableOnly ?: null,
            ]);

            $rawResults         = $service->analyze($server->slug, $filters);
            $this->results      = array_map(fn($r) => $r->jsonSerialize(), $rawResults);
            $this->totalResults = count($this->results);
            $this->analyzed     = true;
            $this->currentPage  = 1;

            $service->saveAnalysis($server, $filters, $rawResults, 0);

            $this->dispatch('results-updated', [
                'labels' => array_column(array_slice($this->results, 0, 10), 'itemName'),
                'data'   => array_column(array_slice($this->results, 0, 10), 'profit'),
            ]);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getPagedResultsProperty(): LengthAwarePaginator
    {
        $sorted = $this->results;

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
            'pagedResults'   => $this->pagedResults,
            'sortColumn'     => $this->sortColumn,
            'sortDirection'  => $this->sortDirection,
        ]);
    }
}
