<?php

namespace App\Livewire;

use App\Clients\UniversalisClient;
use App\Enums\Job;
use App\Models\Recipe;
use App\Models\Server;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RecipeBrowser extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: null)]
    public ?int $jobId = null;

    #[Url(except: 1)]
    public int $minLevel = 1;

    #[Url(except: 100)]
    public int $maxLevel = 100;

    #[Url(except: null)]
    public ?int $stars = null;

    #[Url(except: null)]
    public ?int $serverId = null;

    #[Url(except: 'name')]
    public string $sortColumn = 'name';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingJobId(): void         { $this->resetPage(); }
    public function updatingMinLevel(): void      { $this->resetPage(); }
    public function updatingMaxLevel(): void      { $this->resetPage(); }
    public function updatingStars(): void         { $this->resetPage(); }
    public function updatingServerId(): void      { $this->resetPage(); }
    public function updatingSortColumn(): void    { $this->resetPage(); }
    public function updatingSortDirection(): void { $this->resetPage(); }

    public function sortBy(string $column): void
    {
        $allowed = ['name', 'craft_level', 'stars', 'yields', 'difficulty'];
        if (!in_array($column, $allowed, true)) {
            return;
        }
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn    = $column;
            $this->sortDirection = $column === 'name' ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function render(UniversalisClient $universalis)
    {
        $query = Recipe::with([
            'item:id,name,icon,is_craftable',
            'item.recipes:item_id,job_id,craft_level,stars,difficulty',
            'item.asIngredientIn:recipes.id,recipes.item_id,recipes.job_id,recipes.craft_level',
            'item.asIngredientIn.item:id,name',
            'materials:id,name,icon,is_craftable',
            'materials.gatheringItem:item_id,gathering_level,stars,source',
            'materials.recipe:item_id,job_id,craft_level',
        ])
        ->join('items', 'items.id', '=', 'recipes.item_id')
        ->select('recipes.*')
        ->whereRaw('recipes.id = (SELECT MIN(r2.id) FROM recipes r2 WHERE r2.item_id = recipes.item_id)');

        if ($this->search !== '') {
            $query->where('items.name', 'like', '%' . $this->search . '%');
        }

        if ($this->jobId !== null) {
            $query->where('recipes.job_id', $this->jobId);
        }

        if ($this->minLevel > 0) {
            $query->where('recipes.craft_level', '>=', $this->minLevel);
        }
        if ($this->maxLevel > 0) {
            $query->where('recipes.craft_level', '<=', $this->maxLevel);
        }

        if ($this->stars !== null) {
            $query->where('recipes.stars', $this->stars);
        }

        $col = match ($this->sortColumn) {
            'craft_level' => 'recipes.craft_level',
            'stars'       => 'recipes.stars',
            'yields'      => 'recipes.yields',
            'difficulty'  => 'recipes.difficulty',
            default       => 'items.name',
        };
        $query->orderBy($col, $this->sortDirection);
        if ($this->sortColumn !== 'name') {
            $query->orderBy('items.name', 'asc');
        }

        $recipes = $query->paginate(25);

        $prices = [];
        $server = null;
        if ($this->serverId) {
            $server = Server::find($this->serverId);
            if ($server) {
                $allIds = $recipes->flatMap(
                    fn($r) => $r->materials->pluck('id')->push($r->item_id)
                )->unique()->values()->all();

                if (!empty($allIds)) {
                    $prices = $universalis->getPrices($server->slug, $allIds);
                }
            }
        }

        return view('livewire.recipe-browser', [
            'recipes'       => $recipes,
            'servers'       => Server::active()->orderBy('region')->orderBy('name')->get(),
            'jobs'          => Job::cases(),
            'prices'        => $prices,
            'sortColumn'    => $this->sortColumn,
            'sortDirection' => $this->sortDirection,
        ]);
    }
}
