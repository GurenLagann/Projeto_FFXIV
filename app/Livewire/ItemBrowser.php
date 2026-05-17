<?php

namespace App\Livewire;

use App\Clients\UniversalisClient;
use App\Enums\Job;
use App\Models\Item;
use App\Models\Server;
use Livewire\Component;
use Livewire\WithPagination;

class ItemBrowser extends Component
{
    use WithPagination;

    public string  $tab      = 'craft';
    public string  $search   = '';
    public ?int    $serverId = null;
    public ?int    $jobId    = null;
    public ?string $source   = null;   // 'gathering' | 'fishing'
    public ?int    $minLevel = null;
    public ?int    $maxLevel = null;
    public ?int    $stars    = null;   // null = all, -1 = no stars, 1-3 = stars

    protected $queryString = [
        'tab'      => ['except' => 'craft'],
        'search'   => ['except' => ''],
        'serverId' => ['except' => null],
        'jobId'    => ['except' => null],
        'source'   => ['except' => null],
        'minLevel' => ['except' => null],
        'maxLevel' => ['except' => null],
        'stars'    => ['except' => null],
    ];

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingServerId(): void { $this->resetPage(); }
    public function updatingJobId(): void    { $this->resetPage(); }
    public function updatingSource(): void   { $this->resetPage(); }
    public function updatingMinLevel(): void { $this->resetPage(); }
    public function updatingMaxLevel(): void { $this->resetPage(); }
    public function updatingStars(): void    { $this->resetPage(); }

    public function setTab(string $tab): void
    {
        $this->tab    = $tab;
        $this->jobId  = null;
        $this->source = null;
        $this->stars  = null;
        $this->search = '';
        $this->resetPage();
    }

    public function toggleJob(?int $jobId): void
    {
        $this->jobId = ($this->jobId === $jobId) ? null : $jobId;
        $this->resetPage();
    }

    public function toggleSource(?string $source): void
    {
        $this->source = ($this->source === $source) ? null : $source;
        $this->resetPage();
    }

    public function toggleStars(?int $stars): void
    {
        $this->stars = ($this->stars === $stars) ? null : $stars;
        $this->resetPage();
    }

    public function render(UniversalisClient $universalis)
    {
        $items = $this->tab === 'craft'
            ? $this->craftQuery()->paginate(50)
            : $this->gatheringQuery()->paginate(50);

        $prices = [];
        if ($this->serverId && $items->isNotEmpty()) {
            $server = Server::find($this->serverId);
            if ($server) {
                $ids    = $items->pluck('id')->map(fn($id) => (int) $id)->all();
                $prices = $universalis->getPrices($server->slug, $ids);
            }
        }

        return view('livewire.item-browser', [
            'items'   => $items,
            'prices'  => $prices,
            'servers' => Server::active()->orderBy('region')->orderBy('name')->get(['id', 'name', 'region']),
            'jobs'    => collect(Job::cases())->filter(fn($j) => $j !== Job::OMNICRAFTER)->values(),
        ]);
    }

    private function craftQuery()
    {
        $query = Item::query()
            ->select('id', 'name', 'icon')
            ->whereHas('recipe')
            ->with([
                'recipe:item_id,job_id,craft_level,yields,stars',
                'recipeLookup:item_id,crp_id,bsm_id,arm_id,gsm_id,ltw_id,wvr_id,alc_id,cul_id',
            ]);

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->jobId) {
            $col = $this->jobColumn($this->jobId);
            if ($col) {
                $query->whereHas('recipeLookup', fn($q) => $q->whereNotNull($col));
            }
            if ($this->minLevel || $this->maxLevel) {
                $query->whereHas('recipe', function ($q) {
                    $q->where('job_id', $this->jobId);
                    if ($this->minLevel) $q->where('craft_level', '>=', $this->minLevel);
                    if ($this->maxLevel) $q->where('craft_level', '<=', $this->maxLevel);
                });
            }
        } else {
            if ($this->minLevel) {
                $query->whereHas('recipe', fn($q) => $q->where('craft_level', '>=', $this->minLevel));
            }
            if ($this->maxLevel) {
                $query->whereHas('recipe', fn($q) => $q->where('craft_level', '<=', $this->maxLevel));
            }
        }

        if ($this->stars !== null) {
            $starsValue = $this->stars === -1 ? 0 : $this->stars;
            $query->whereHas('recipe', fn($q) => $q->where('stars', $starsValue));
        }

        return $query->orderBy('name');
    }

    private function gatheringQuery()
    {
        $query = Item::query()
            ->select('id', 'name', 'icon')
            ->whereHas('gatheringItem')
            ->with('gatheringItem:item_id,gathering_level,stars,source');

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->source) {
            $query->whereHas('gatheringItem', fn($q) => $q->where('source', $this->source));
        }

        if ($this->minLevel) {
            $query->whereHas('gatheringItem', fn($q) => $q->where('gathering_level', '>=', $this->minLevel));
        }

        if ($this->maxLevel) {
            $query->whereHas('gatheringItem', fn($q) => $q->where('gathering_level', '<=', $this->maxLevel));
        }

        if ($this->stars !== null) {
            $starsValue = $this->stars === -1 ? 0 : $this->stars;
            $query->whereHas('gatheringItem', fn($q) => $q->where('stars', $starsValue));
        }

        return $query->orderBy('name');
    }

    private function jobColumn(int $jobId): ?string
    {
        return match($jobId) {
            8  => 'crp_id',
            9  => 'bsm_id',
            10 => 'arm_id',
            11 => 'gsm_id',
            12 => 'ltw_id',
            13 => 'wvr_id',
            14 => 'alc_id',
            15 => 'cul_id',
            default => null,
        };
    }
}
