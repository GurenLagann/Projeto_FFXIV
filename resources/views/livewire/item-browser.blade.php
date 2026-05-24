<div>
    {{-- ── Header ── --}}
    <div class="mb-6">
        <h1 class="ff-title text-2xl mb-1">Item Browser</h1>
        <p class="text-[var(--dim)] text-xs tracking-wide">
            Catálogo de itens craftáveis e coletáveis de Eorzea
        </p>
    </div>

    {{-- ── Tab switcher ── --}}
    <div class="flex gap-0 mb-6 border border-[var(--border)] w-fit">
        <button wire:click="setTab('craft')"
                class="px-6 py-2.5 ff-label transition-colors
                       {{ $tab === 'craft'
                           ? 'bg-[var(--hi)] text-[var(--crystal)] border-r border-[var(--border)]'
                           : 'text-[var(--dim)] hover:text-[var(--text)] border-r border-[var(--border)]' }}">
            ⚒ Craft
        </button>
        <button wire:click="setTab('gathering')"
                class="px-6 py-2.5 ff-label transition-colors
                       {{ $tab === 'gathering'
                           ? 'bg-[var(--hi)] text-[var(--crystal)]'
                           : 'text-[var(--dim)] hover:text-[var(--text)]' }}">
            ⛏ Coleta
        </button>
    </div>

    {{-- ── Filters ── --}}
    <div class="ff-box p-4 mb-4 space-y-3">

        {{-- Row 1: Search + Server + Level + Stars --}}
        <div class="flex flex-wrap gap-3 items-end">

            <div class="flex-1 min-w-48">
                <label class="ff-label block mb-1">Buscar item</label>
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Nome do item..."
                       class="ff-input" />
            </div>

            <div class="min-w-44">
                <label class="ff-label block mb-1">Servidor (preços)</label>
                <select wire:model.live="serverId" class="ff-input">
                    <option value="">— Sem preços —</option>
                    @php $currentRegion = null; @endphp
                    @foreach($servers as $server)
                        @if($currentRegion !== $server->region)
                            @if($currentRegion !== null)</optgroup>@endif
                            <optgroup label="{{ $server->region }}">
                            @php $currentRegion = $server->region; @endphp
                        @endif
                        <option value="{{ $server->id }}">{{ $server->name }}</option>
                    @endforeach
                    @if($currentRegion !== null)</optgroup>@endif
                </select>
            </div>

            <div class="flex gap-2 items-end">
                <div>
                    <label class="ff-label block mb-1">Nível mín.</label>
                    <input wire:model.live.debounce.400ms="minLevel"
                           type="number" min="1" max="100"
                           placeholder="1"
                           class="ff-input w-20 text-center" />
                </div>
                <span class="text-[var(--dim)] pb-2">–</span>
                <div>
                    <label class="ff-label block mb-1">Nível máx.</label>
                    <input wire:model.live.debounce.400ms="maxLevel"
                           type="number" min="1" max="100"
                           placeholder="100"
                           class="ff-input w-20 text-center" />
                </div>
            </div>

            <div>
                <label class="ff-label block mb-1">Stars</label>
                <div class="flex gap-1">
                    @foreach([null => 'Todos', -1 => '○', 1 => '★', 2 => '★★', 3 => '★★★'] as $val => $label)
                        <button wire:click="toggleStars({{ $val === null ? 'null' : $val }})"
                                class="px-2 py-1 text-[0.6rem] border transition-colors
                                       {{ $stars === ($val === null ? null : (int)$val)
                                           ? 'border-[var(--gold)] text-[var(--gold)] bg-[rgba(240,192,48,0.08)]'
                                           : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Row 2: Job / Source filter buttons --}}
        @if($tab === 'craft')
            <div>
                <label class="ff-label block mb-1.5">Classe</label>
                <div class="flex flex-wrap gap-1.5">
                    <button wire:click="toggleJob(null)"
                            class="px-3 py-1 ff-label border transition-colors
                                   {{ $jobId === null
                                       ? 'border-[var(--crystal)] text-[var(--crystal)] bg-[rgba(85,153,255,0.08)]'
                                       : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                        Todos
                    </button>
                    @foreach($jobs as $job)
                        <button wire:click="toggleJob({{ $job->value }})"
                                class="px-3 py-1 ff-label border transition-colors
                                       {{ $jobId === $job->value
                                           ? 'border-[var(--crystal)] text-[var(--crystal)] bg-[rgba(85,153,255,0.08)]'
                                           : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                            {{ $job->getAbbreviation() }}
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <div>
                <label class="ff-label block mb-1.5">Fonte</label>
                <div class="flex gap-1.5">
                    {{-- Todos --}}
                    <button wire:click="toggleSource(null)"
                            class="px-3 py-1 ff-label border transition-colors
                                   {{ $source === null
                                       ? 'border-[var(--hi)] text-[var(--text)] bg-[rgba(255,255,255,0.05)]'
                                       : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                        Todos
                    </button>

                    {{-- Mineração / Botânica --}}
                    <button wire:click="toggleSource('gathering')"
                            class="px-3 py-1 ff-label border transition-colors
                                   {{ $source === 'gathering'
                                       ? 'border-[var(--mako)] text-[var(--mako)] bg-[rgba(0,221,119,0.06)]'
                                       : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                        ⛏ MIN / BTN
                    </button>

                    {{-- Pesca --}}
                    <button wire:click="toggleSource('fishing')"
                            class="px-3 py-1 ff-label border transition-colors
                                   {{ $source === 'fishing'
                                       ? 'border-[var(--crystal)] text-[var(--crystal)] bg-[rgba(85,153,255,0.08)]'
                                       : 'border-[var(--border)] text-[var(--dim)] hover:border-[var(--hi)] hover:text-[var(--text)]' }}">
                        🎣 FSH
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Result count + loading ── --}}
    <div class="flex items-center justify-between mb-3">
        <p class="ff-label" style="color:var(--dim);">
            {{ number_format($items->total()) }} itens encontrados
            @if($serverId && empty($prices))
                <span class="ml-2 text-[var(--gold)]">· buscando preços...</span>
            @elseif($serverId && !empty($prices))
                <span class="ml-2" style="color:#2a4a30;">· preços: {{ count($prices) }} itens</span>
            @endif
        </p>
        <div wire:loading class="flex items-center gap-2">
            <div class="atb-track w-24"><div class="atb-fill"></div></div>
            <span class="ff-label" style="color:var(--dim);font-size:0.55rem;">carregando</span>
        </div>
    </div>

    {{-- ── Table ── --}}
    @php
        $si      = fn(string $col) => $sortColumn === $col ? ($sortDirection === 'asc' ? '▲' : '▼') : '⇅';
        $thA     = 'color:#ccd4f0;';
        $thB     = 'color:#3a4870;';
        $thClass = 'cursor-pointer select-none';
    @endphp
    <div class="ff-box overflow-hidden">
        <div class="overflow-x-auto">
            @if($tab === 'craft')
            <table class="ff-table w-full">
                <thead>
                    <tr>
                        <th class="text-left {{ $thClass }}"
                            wire:click="sortBy('name')"
                            style="{{ $sortColumn === 'name' ? $thA : $thB }}">
                            <span class="inline-flex items-center gap-1">
                                Item
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'name' ? '1' : '0.35' }};">{{ $si('name') }}</span>
                            </span>
                        </th>
                        <th class="text-left">Classes</th>
                        <th class="text-center {{ $thClass }}"
                            wire:click="sortBy('level')"
                            style="{{ $sortColumn === 'level' ? $thA : $thB }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Nível
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'level' ? '1' : '0.35' }};">{{ $si('level') }}</span>
                            </span>
                        </th>
                        <th class="text-center {{ $thClass }}"
                            wire:click="sortBy('stars')"
                            style="{{ $sortColumn === 'stars' ? $thA : $thB }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Stars
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'stars' ? '1' : '0.35' }};">{{ $si('stars') }}</span>
                            </span>
                        </th>
                        <th class="text-center">Yield</th>
                        @if($serverId)
                            <th class="text-right">Preço mín. NQ</th>
                            <th class="text-right">Mediana NQ</th>
                            <th class="text-right">Vendas/sem</th>
                            <th class="text-right">Atualizado</th>
                            <th class="w-8"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $recipe  = $item->recipe;
                            $lookup  = $item->recipeLookup;
                            $abbrs   = $lookup?->getJobAbbreviations() ?? [];
                            $price   = $prices[(int)$item->id] ?? null;
                            $iconUrl = $item->iconUrl;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($iconUrl)
                                        <img src="{{ $iconUrl }}" alt="" class="w-8 h-8 object-contain flex-shrink-0" loading="lazy"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <span class="text-[var(--text)] font-medium text-sm">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($abbrs as $abbr)
                                        <span class="ff-badge-neutral text-[0.6rem]">{{ $abbr }}</span>
                                    @empty
                                        @if($recipe)
                                            <span class="ff-badge-neutral text-[0.6rem]">
                                                {{ \App\Enums\Job::tryFrom($recipe->job_id)?->getAbbreviation() ?? '?' }}
                                            </span>
                                        @endif
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-center ff-num text-sm">{{ $recipe?->craft_level ?? '—' }}</td>
                            <td class="text-center text-[var(--gold)] text-xs tracking-tighter">
                                {{ $recipe && $recipe->stars > 0 ? str_repeat('★', $recipe->stars) : '' }}
                            </td>
                            <td class="text-center ff-num text-sm text-[var(--dim)]">
                                {{ $recipe?->yields > 1 ? 'x' . $recipe->yields : '—' }}
                            </td>
                            @if($serverId)
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->minPriceNQ > 0)
                                        <span class="text-[var(--mako)]">{{ number_format($price->minPriceNQ) }}</span>
                                        <span class="text-[var(--dim)] text-xs ml-0.5">g</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->medianSalePriceNQ > 0)
                                        <span class="text-[var(--crystal)]">{{ number_format($price->medianSalePriceNQ) }}</span>
                                        <span class="text-[var(--dim)] text-xs ml-0.5">g</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->salesPerWeek > 0)
                                        <span class="{{ $price->salesPerWeek >= 10 ? 'text-[var(--mako)]' : 'text-[var(--text)]' }}">
                                            {{ number_format($price->salesPerWeek, 1) }}
                                        </span>
                                        <span class="text-[var(--dim)] text-xs">/sem</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right text-xs text-[var(--dim)]">
                                    @if($price?->lastUploadTime)
                                        <span title="{{ \Carbon\Carbon::createFromTimestamp($price->lastUploadTime)->format('d/m/Y H:i') }}">
                                            {{ \Carbon\Carbon::createFromTimestamp($price->lastUploadTime)->diffForHumans() }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="w-8 text-center">
                                    <button wire:click="refreshPrice({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="refreshPrice({{ $item->id }})"
                                            title="Atualizar preço deste item"
                                            class="text-[var(--dim)] hover:text-[var(--crystal)] transition-colors disabled:opacity-40 disabled:cursor-wait">
                                        <span wire:loading.remove wire:target="refreshPrice({{ $item->id }})" style="font-size:0.85rem;">↻</span>
                                        <span wire:loading wire:target="refreshPrice({{ $item->id }})" style="font-size:0.85rem;" class="inline-block animate-spin">↻</span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $serverId ? 10 : 5 }}" class="text-center py-12 text-[var(--dim)] ff-label">
                                Nenhum item encontrado com os filtros selecionados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @else
            <table class="ff-table w-full">
                <thead>
                    <tr>
                        <th class="text-left {{ $thClass }}"
                            wire:click="sortBy('name')"
                            style="{{ $sortColumn === 'name' ? $thA : $thB }}">
                            <span class="inline-flex items-center gap-1">
                                Item
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'name' ? '1' : '0.35' }};">{{ $si('name') }}</span>
                            </span>
                        </th>
                        <th class="text-center">Fonte</th>
                        <th class="text-center {{ $thClass }}"
                            wire:click="sortBy('level')"
                            style="{{ $sortColumn === 'level' ? $thA : $thB }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Nível
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'level' ? '1' : '0.35' }};">{{ $si('level') }}</span>
                            </span>
                        </th>
                        <th class="text-center {{ $thClass }}"
                            wire:click="sortBy('stars')"
                            style="{{ $sortColumn === 'stars' ? $thA : $thB }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Stars
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'stars' ? '1' : '0.35' }};">{{ $si('stars') }}</span>
                            </span>
                        </th>
                        @if($serverId)
                            <th class="text-right">Preço mín. NQ</th>
                            <th class="text-right">Mediana NQ</th>
                            <th class="text-right">Vendas/sem</th>
                            <th class="text-right">Atualizado</th>
                            <th class="w-8"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $gi      = $item->gatheringItem;
                            $price   = $prices[(int)$item->id] ?? null;
                            $iconUrl = $item->iconUrl;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($iconUrl)
                                        <img src="{{ $iconUrl }}" alt="" class="w-8 h-8 object-contain flex-shrink-0" loading="lazy"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <span class="text-[var(--text)] font-medium text-sm">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($gi)
                                    <span class="ff-label px-2 py-0.5 border
                                        {{ $gi->source === 'fishing'
                                            ? 'border-[rgba(85,153,255,0.4)] text-[var(--crystal)] bg-[rgba(85,153,255,0.06)]'
                                            : 'border-[rgba(0,221,119,0.35)] text-[var(--mako)] bg-[rgba(0,50,25,0.4)]' }}">
                                        {{ $gi->source_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center ff-num text-sm">{{ $gi?->gathering_level ?? '—' }}</td>
                            <td class="text-center text-[var(--gold)] text-xs tracking-tighter">
                                {{ $gi && $gi->stars > 0 ? str_repeat('★', $gi->stars) : '' }}
                            </td>
                            @if($serverId)
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->minPriceNQ > 0)
                                        <span class="text-[var(--mako)]">{{ number_format($price->minPriceNQ) }}</span>
                                        <span class="text-[var(--dim)] text-xs ml-0.5">g</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->medianSalePriceNQ > 0)
                                        <span class="text-[var(--crystal)]">{{ number_format($price->medianSalePriceNQ) }}</span>
                                        <span class="text-[var(--dim)] text-xs ml-0.5">g</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right ff-num text-sm">
                                    @if($price && $price->salesPerWeek > 0)
                                        <span class="{{ $price->salesPerWeek >= 10 ? 'text-[var(--mako)]' : 'text-[var(--text)]' }}">
                                            {{ number_format($price->salesPerWeek, 1) }}
                                        </span>
                                        <span class="text-[var(--dim)] text-xs">/sem</span>
                                    @else
                                        <span class="text-[var(--dim)]">—</span>
                                    @endif
                                </td>
                                <td class="text-right text-xs text-[var(--dim)]">
                                    @if($price?->lastUploadTime)
                                        <span title="{{ \Carbon\Carbon::createFromTimestamp($price->lastUploadTime)->format('d/m/Y H:i') }}">
                                            {{ \Carbon\Carbon::createFromTimestamp($price->lastUploadTime)->diffForHumans() }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="w-8 text-center">
                                    <button wire:click="refreshPrice({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="refreshPrice({{ $item->id }})"
                                            title="Atualizar preço deste item"
                                            class="text-[var(--dim)] hover:text-[var(--crystal)] transition-colors disabled:opacity-40 disabled:cursor-wait">
                                        <span wire:loading.remove wire:target="refreshPrice({{ $item->id }})" style="font-size:0.85rem;">↻</span>
                                        <span wire:loading wire:target="refreshPrice({{ $item->id }})" style="font-size:0.85rem;" class="inline-block animate-spin">↻</span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $serverId ? 9 : 4 }}" class="text-center py-12 text-[var(--dim)] ff-label">
                                Nenhum item encontrado com os filtros selecionados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- ── Pagination ── --}}
    @if($items->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <span class="ff-label" style="color:var(--dim);font-size:0.6rem;">
                Página {{ $items->currentPage() }} de {{ $items->lastPage() }}
                &nbsp;·&nbsp; {{ number_format($items->total()) }} itens
            </span>
            <div class="flex gap-1">
                <button wire:click="previousPage"
                        @if($items->onFirstPage()) disabled @endif
                        class="ff-btn-ghost px-3 py-1.5 text-[0.6rem]
                               {{ $items->onFirstPage() ? 'opacity-25 cursor-not-allowed' : '' }}">
                    ‹ Anterior
                </button>
                <button wire:click="nextPage"
                        @if(!$items->hasMorePages()) disabled @endif
                        class="ff-btn-ghost px-3 py-1.5 text-[0.6rem]
                               {{ !$items->hasMorePages() ? 'opacity-25 cursor-not-allowed' : '' }}">
                    Próximo ›
                </button>
            </div>
        </div>
    @endif
</div>
