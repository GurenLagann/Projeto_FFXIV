<div>

{{-- ══════════════════════════════════════════════════════
     CARD DE FILTROS
     ══════════════════════════════════════════════════════ --}}
<div class="ff-box mb-6">

    {{-- ── Header ── --}}
    <div class="flex items-center px-6 pt-5 pb-4">
        <div class="flex items-center gap-2">
            <span style="color:#4040a0;font-size:0.55rem;" aria-hidden="true">◆</span>
            <span class="ff-label" style="font-size:0.6rem;color:#3a4870;letter-spacing:0.2em;">GUIA DE CRAFTING</span>
        </div>
    </div>

    <div class="ff-divider mx-6"></div>

    {{-- ── Busca + Servidor ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 px-6 pt-5 pb-0">

        {{-- Busca --}}
        <div>
            <div class="flex items-center gap-2 mb-2.5">
                <div style="height:1px;width:10px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.5rem;color:#3a4870;letter-spacing:0.2em;">BUSCAR ITEM</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Nome do item..."
                   style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                          padding:0.55rem 0.8rem;font-size:0.875rem;width:100%;border-radius:0;">
        </div>

        {{-- Servidor (para preços) --}}
        <div>
            <div class="flex items-center gap-2 mb-2.5">
                <div style="height:1px;width:10px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.5rem;color:#3a4870;letter-spacing:0.2em;">SERVIDOR <span style="color:#2a2a50;">(PREÇOS)</span></span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <select wire:model.live="serverId"
                    style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                           padding:0.55rem 0.8rem;font-size:0.875rem;width:100%;border-radius:0;">
                <option value="">── sem preços ──</option>
                @foreach($servers->groupBy('region') as $region => $regionServers)
                    <optgroup label="{{ $region }}">
                        @foreach($regionServers->groupBy('datacenter') as $dc => $dcServers)
                            <optgroup label="  ↳ {{ $dc }}">
                                @foreach($dcServers as $server)
                                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Job Grid ── --}}
    <div class="px-6 pt-5 pb-0">
        <div class="flex items-center gap-2 mb-2.5">
            <div style="height:1px;width:10px;background:linear-gradient(90deg,transparent,#252560);"></div>
            <span class="ff-label" style="font-size:0.5rem;color:#3a4870;letter-spacing:0.2em;">JOB DE CRAFTING</span>
            <div style="height:1px;flex:1;background:#252560;"></div>
        </div>
        <div class="grid gap-1" style="grid-template-columns:repeat(5,1fr);">

            {{-- ALL --}}
            <button type="button" wire:click="$set('jobId', null)"
                    title="Todos os Jobs"
                    style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                           gap:2px;padding:0.4rem 0.15rem;border:1px solid;cursor:pointer;
                           border-color:{{ $jobId === null ? '#f0c030' : '#1e1e40' }};
                           background:{{ $jobId === null ? 'rgba(240,192,48,0.09)' : 'rgba(4,4,14,0.8)' }};
                           transition:border-color 100ms,background 100ms;">
                <span style="font-size:0.95rem;line-height:1;color:{{ $jobId === null ? '#f0c030' : '#2a3050' }};">✦</span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.46rem;color:{{ $jobId === null ? '#f0c030' : '#3a4460' }};">ALL</span>
            </button>

            @foreach($jobs as $job)
                @if($job->value !== 0)
                <button type="button" wire:click="$set('jobId', {{ $job->value }})"
                        title="{{ $job->getLabel() }}"
                        style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                               gap:2px;padding:0.4rem 0.1rem;border:1px solid;cursor:pointer;
                               border-color:{{ $jobId === $job->value ? '#f0c030' : '#1e1e40' }};
                               background:{{ $jobId === $job->value ? 'rgba(240,192,48,0.09)' : 'rgba(4,4,14,0.8)' }};
                               transition:border-color 100ms,background 100ms;">
                    <img src="{{ $job->getIconUrl() }}" alt="{{ $job->getAbbreviation() }}"
                         width="22" height="22"
                         style="image-rendering:pixelated;opacity:{{ $jobId === $job->value ? '1' : '0.35' }};transition:opacity 100ms;">
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.44rem;line-height:1;
                                 color:{{ $jobId === $job->value ? '#f0c030' : '#3a4460' }};">
                        {{ $job->getAbbreviation() }}
                    </span>
                </button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Nível + Estrelas ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 px-6 py-5">

        {{-- Nível --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">NÍVEL</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <div class="flex items-center gap-1.5">
                <input type="number" wire:model.live.debounce.400ms="minLevel" min="1" max="100"
                       aria-label="Nível mínimo" placeholder="1"
                       style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                              padding:0.42rem 0.4rem;font-size:0.82rem;border-radius:0;width:100%;
                              text-align:center;font-family:'Share Tech Mono',monospace;">
                <span style="color:#252560;font-size:0.55rem;flex-shrink:0;">–</span>
                <input type="number" wire:model.live.debounce.400ms="maxLevel" min="1" max="100"
                       aria-label="Nível máximo" placeholder="100"
                       style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                              padding:0.42rem 0.4rem;font-size:0.82rem;border-radius:0;width:100%;
                              text-align:center;font-family:'Share Tech Mono',monospace;">
            </div>
        </div>

        {{-- Estrelas --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">DIFICULDADE</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <div class="flex gap-1">
                @foreach([[null,'Todos'], [0,'○'], [1,'★'], [2,'★★'], [3,'★★★']] as [$val, $lbl])
                    @php $isActive = $stars === $val; @endphp
                    <button type="button"
                            wire:click="$set('stars', {{ $val === null ? 'null' : $val }})"
                            style="flex:1;padding:0.42rem 0.2rem;border:1px solid;cursor:pointer;
                                   font-family:'Share Tech Mono',monospace;font-size:0.65rem;
                                   color:{{ $isActive ? '#f0c030' : '#3a4460' }};
                                   border-color:{{ $isActive ? '#f0c030' : '#1e1e40' }};
                                   background:{{ $isActive ? 'rgba(240,192,48,0.09)' : 'rgba(4,4,14,0.8)' }};
                                   transition:border-color 100ms,background 100ms;">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ATB LOADING
     ══════════════════════════════════════════════════════ --}}
<div wire:loading wire:target="search,jobId,minLevel,maxLevel,stars" class="ff-box mb-4 p-5" aria-label="Carregando receitas..." role="status">
    <div class="flex items-center gap-3 mb-3">
        <span style="color:#5599ff;font-size:0.6rem;letter-spacing:0.15em;font-family:'Cinzel',serif;text-transform:uppercase;">
            ► Buscando receitas...
        </span>
    </div>
    <div class="atb-track"><div class="atb-fill"></div></div>
    <div class="mt-4 space-y-2.5">
        @for($i = 0; $i < 5; $i++)
            <div class="flex items-center gap-4">
                <div class="ff-skeleton" style="width:20px;height:20px;flex-shrink:0;"></div>
                <div class="ff-skeleton h-3 flex-1 max-w-[220px]"></div>
                <div class="ff-skeleton h-3 w-10"></div>
                <div class="ff-skeleton h-3 w-14"></div>
                <div class="ff-skeleton h-3 w-16"></div>
                <div class="ff-skeleton h-3 w-14 ml-auto"></div>
            </div>
        @endfor
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     RESULTADOS
     ══════════════════════════════════════════════════════ --}}
<div wire:loading.remove wire:target="search,jobId,minLevel,maxLevel,stars">

@if($recipes->count() > 0)
    <div class="ff-box overflow-hidden">

        {{-- Header da tabela --}}
        <div class="flex items-center justify-between px-5 py-3.5"
             style="border-bottom:1px solid #252560;">
            <div class="flex items-center gap-3">
                <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
                <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Receitas</h2>
                <span class="ff-badge-neutral" style="font-size:0.65rem;">{{ $recipes->total() }}</span>
            </div>
            @if($recipes->hasPages())
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:var(--dim);">
                    {{ $recipes->currentPage() }} / {{ $recipes->lastPage() }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Lista de receitas de crafting">
                @php
                    $sortIcon = fn(string $col) => $sortColumn === $col
                        ? ($sortDirection === 'asc' ? '▲' : '▼')
                        : '⇅';
                    $thActive = 'color:#ccd4f0;';
                    $thBase   = 'color:#3a4870;';
                @endphp
                <thead>
                    <tr>
                        <th scope="col" class="text-left cursor-pointer select-none"
                            wire:click="sortBy('name')"
                            style="{{ $sortColumn === 'name' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center gap-1">
                                Item
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'name' ? '1' : '0.35' }};">{{ $sortIcon('name') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-center" style="width:3.5rem;">Job</th>
                        <th scope="col" class="text-center cursor-pointer select-none"
                            wire:click="sortBy('craft_level')"
                            style="width:4rem;{{ $sortColumn === 'craft_level' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Nível
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'craft_level' ? '1' : '0.35' }};">{{ $sortIcon('craft_level') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-center hidden sm:table-cell cursor-pointer select-none"
                            wire:click="sortBy('stars')"
                            style="width:3rem;{{ $sortColumn === 'stars' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                ★
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'stars' ? '1' : '0.35' }};">{{ $sortIcon('stars') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-center hidden md:table-cell cursor-pointer select-none"
                            wire:click="sortBy('yields')"
                            style="width:3.5rem;{{ $sortColumn === 'yields' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-center gap-1">
                                Yields
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'yields' ? '1' : '0.35' }};">{{ $sortIcon('yields') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-right hidden lg:table-cell cursor-pointer select-none"
                            wire:click="sortBy('difficulty')"
                            style="{{ $sortColumn === 'difficulty' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Dificuldade
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'difficulty' ? '1' : '0.35' }};">{{ $sortIcon('difficulty') }}</span>
                            </span>
                        </th>
                        <th scope="col" style="width:2.5rem;"></th>
                    </tr>
                </thead>

                @foreach($recipes as $recipe)
                    @php
                        $starsStr = $recipe->stars > 0 ? str_repeat('★', $recipe->stars) : '';
                        $jobEnum  = $recipe->job;
                    @endphp
                    <tbody x-data="{ open: false }">
                        {{-- ── Linha principal ── --}}
                        <tr @click="open = !open"
                            style="cursor:pointer;"
                            :style="open ? 'background:rgba(64,64,160,0.12)' : ''">

                            {{-- Item --}}
                            <td class="font-medium">
                                <div class="flex items-center gap-2" title="{{ $recipe->item->name ?? '—' }}">
                                    @if($recipe->item?->iconUrl)
                                        <img src="{{ $recipe->item->iconUrl }}"
                                             alt="" width="20" height="20"
                                             style="flex-shrink:0;image-rendering:pixelated;opacity:0.9;"
                                             loading="lazy"
                                             onerror="this.style.display='none'">
                                    @endif
                                    <span class="truncate block max-w-[180px] md:max-w-[300px]"
                                          style="color:#ccd4f0;">
                                        {{ $recipe->item->name ?? '—' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Job --}}
                            <td class="text-center whitespace-nowrap">
                                @if($jobEnum && $jobEnum->getIconUrl())
                                    <div class="flex flex-col items-center gap-0.5">
                                        <img src="{{ $jobEnum->getIconUrl() }}" alt="{{ $jobEnum->getAbbreviation() }}"
                                             width="18" height="18"
                                             style="image-rendering:pixelated;opacity:0.7;">
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.42rem;color:#3a4460;">
                                            {{ $jobEnum->getAbbreviation() }}
                                        </span>
                                    </div>
                                @elseif($jobEnum)
                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a4460;">
                                        {{ $jobEnum->getAbbreviation() }}
                                    </span>
                                @endif
                            </td>

                            {{-- Nível --}}
                            <td class="text-center whitespace-nowrap ff-num"
                                style="color:#8899cc;font-size:0.82rem;">
                                {{ $recipe->craft_level }}
                            </td>

                            {{-- Estrelas --}}
                            <td class="text-center hidden sm:table-cell whitespace-nowrap"
                                style="color:#f0c030;font-size:0.65rem;letter-spacing:-0.05em;">
                                {{ $starsStr ?: '—' }}
                            </td>

                            {{-- Yields --}}
                            <td class="text-center hidden md:table-cell whitespace-nowrap ff-num"
                                style="color:var(--dim);font-size:0.78rem;">
                                ×{{ $recipe->yields }}
                            </td>

                            {{-- Dificuldade --}}
                            <td class="text-right hidden lg:table-cell whitespace-nowrap ff-num"
                                style="color:#5599ff;font-size:0.78rem;">
                                {{ $recipe->difficulty > 0 ? number_format($recipe->difficulty) : '—' }}
                                @if($recipe->difficulty > 0)
                                    <span style="font-size:0.6rem;opacity:0.4;">pts</span>
                                @endif
                            </td>

                            {{-- Toggle --}}
                            <td class="text-center">
                                <span style="font-family:'Share Tech Mono',monospace;font-size:0.6rem;
                                             color:#3a4460;transition:transform 150ms;display:inline-block;"
                                      :style="open ? 'transform:rotate(180deg);color:#5599ff' : ''">
                                    ▼
                                </span>
                            </td>
                        </tr>

                        {{-- ── Linha expandida ── --}}
                        <tr x-show="open" style="display:none;">
                            <td colspan="7" style="padding:0;background:rgba(4,4,20,0.7);border-bottom:1px solid #252560;">

                                @php
                                    $usedIn = $recipe->item->asIngredientIn ?? collect();

                                    // Variantes únicas por (job_id, craft_level, stars)
                                    $allVariants = ($recipe->item->recipes ?? collect())
                                        ->unique(fn($r) => $r->job_id . '-' . $r->craft_level . '-' . $r->stars)
                                        ->sortBy(['craft_level', 'stars', 'job_id'])
                                        ->values();

                                    // Agrupadas por (craft_level, stars) para exibição compacta
                                    $variantGroups = $allVariants->groupBy(
                                        fn($r) => $r->craft_level . '-' . $r->stars
                                    );
                                @endphp

                                {{-- ══ SEÇÃO 0: VARIANTES (se houver mais de 1) ══ --}}
                                @if($allVariants->count() > 1)
                                    <div class="flex items-center gap-2 px-6 pt-3 pb-2"
                                         style="border-bottom:1px solid rgba(37,37,96,0.4);">
                                        <span style="font-family:'Cinzel',serif;font-size:0.48rem;letter-spacing:0.2em;
                                                     text-transform:uppercase;color:#2a3460;">CLASSES DISPONÍVEIS</span>
                                        <div style="height:1px;flex:1;background:rgba(37,37,96,0.5);"></div>
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;color:#2e3a60;">
                                            {{ $allVariants->count() }} variante{{ $allVariants->count() !== 1 ? 's' : '' }}
                                        </span>
                                    </div>

                                    <div class="px-6 py-3 space-y-2.5">
                                        @foreach($variantGroups as $groupKey => $group)
                                            @php
                                                $groupLevel = $group->first()->craft_level;
                                                $groupStars = $group->first()->stars;
                                                $starsStr   = $groupStars > 0 ? str_repeat('★', $groupStars) : '○';
                                            @endphp
                                            <div class="flex items-center gap-3 flex-wrap">
                                                {{-- Badge nível + estrelas --}}
                                                <span style="font-family:'Share Tech Mono',monospace;font-size:0.6rem;
                                                             color:#5569a0;border:1px solid rgba(85,105,160,0.3);
                                                             background:rgba(10,12,30,0.5);padding:0.1rem 0.5rem;
                                                             white-space:nowrap;flex-shrink:0;">
                                                    Lv.{{ $groupLevel }}
                                                    <span style="color:#f0c030;margin-left:0.25rem;">{{ $starsStr }}</span>
                                                </span>
                                                {{-- Job badges --}}
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($group as $variant)
                                                        @php $vJob = \App\Enums\Job::tryFrom($variant->job_id); @endphp
                                                        @if($vJob)
                                                            <div style="display:flex;flex-direction:column;align-items:center;
                                                                        gap:2px;padding:0.25rem 0.5rem;
                                                                        border:1px solid rgba(240,192,48,0.2);
                                                                        background:rgba(40,30,0,0.3);">
                                                                <img src="{{ $vJob->getIconUrl() }}"
                                                                     alt="{{ $vJob->getAbbreviation() }}"
                                                                     width="18" height="18"
                                                                     style="image-rendering:pixelated;opacity:0.75;">
                                                                <span style="font-family:'Share Tech Mono',monospace;
                                                                             font-size:0.42rem;color:#c0a030;">
                                                                    {{ $vJob->getAbbreviation() }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                {{-- Dificuldade do grupo (se disponível) --}}
                                                @if($group->first()->difficulty > 0)
                                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;
                                                                 color:#3a4a80;margin-left:auto;">
                                                        {{ number_format($group->first()->difficulty) }} pts
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- ══ SEÇÃO 1: MATERIAIS NECESSÁRIOS ══ --}}
                                <div class="flex items-center gap-2 px-6 pt-3 pb-2"
                                     style="border-bottom:1px solid rgba(37,37,96,0.4);">
                                    <span style="font-family:'Cinzel',serif;font-size:0.48rem;letter-spacing:0.2em;
                                                 text-transform:uppercase;color:#2a3460;">MATERIAIS NECESSÁRIOS</span>
                                    <div style="height:1px;flex:1;background:rgba(37,37,96,0.5);"></div>
                                    @if($recipe->difficulty > 0)
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;color:#5599ff;">
                                            Dific.: {{ number_format($recipe->difficulty) }} pts
                                        </span>
                                    @endif
                                    @if($recipe->can_be_hq)
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;
                                                     color:#00aa55;border:1px solid rgba(0,170,85,0.3);
                                                     padding:0.05rem 0.35rem;">HQ</span>
                                    @endif
                                </div>

                                <div class="px-6 py-3">
                                    @if($recipe->materials->isEmpty())
                                        <p style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#2a3060;">
                                            Sem materiais registrados.
                                        </p>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table style="width:100%;border-collapse:collapse;">
                                                <thead>
                                                    <tr>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:left;padding:0.3rem 0.5rem 0.3rem 0;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Material</th>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:center;padding:0.3rem 0.5rem;width:3rem;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Qtd</th>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:left;padding:0.3rem 0.5rem;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Origem</th>
                                                        @if($serverId)
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:right;padding:0.3rem 0 0.3rem 0.5rem;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Preço NQ</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recipe->materials as $mat)
                                                        @php
                                                            $gathering = $mat->gatheringItem;
                                                            $price     = $prices[$mat->id] ?? null;
                                                        @endphp
                                                        <tr style="border-bottom:1px solid rgba(37,37,96,0.2);">
                                                            <td style="padding:0.45rem 0.5rem 0.45rem 0;">
                                                                <div class="flex items-center gap-1.5">
                                                                    @if($mat->iconUrl)
                                                                        <img src="{{ $mat->iconUrl }}"
                                                                             alt="" width="16" height="16"
                                                                             style="flex-shrink:0;image-rendering:pixelated;opacity:0.8;"
                                                                             loading="lazy"
                                                                             onerror="this.style.display='none'">
                                                                    @endif
                                                                    <span style="font-size:0.8rem;color:#9aa8c8;">{{ $mat->name }}</span>
                                                                </div>
                                                            </td>
                                                            <td style="text-align:center;padding:0.45rem 0.5rem;">
                                                                <span style="font-family:'Share Tech Mono',monospace;font-size:0.75rem;color:var(--dim);">
                                                                    ×{{ $mat->pivot->quantity }}
                                                                </span>
                                                            </td>
                                                            <td style="padding:0.45rem 0.5rem;">
                                                                @if($gathering)
                                                                    @if($gathering->source === 'fishing')
                                                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;color:#5599ff;
                                                                                     border:1px solid rgba(85,153,255,0.25);background:rgba(20,30,60,0.4);
                                                                                     padding:0.1rem 0.4rem;white-space:nowrap;">
                                                                            🎣 FSH Lv.{{ $gathering->gathering_level }}{{ str_repeat('★', $gathering->stars) }}
                                                                        </span>
                                                                    @else
                                                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;color:#00aa55;
                                                                                     border:1px solid rgba(0,170,85,0.25);background:rgba(0,20,10,0.4);
                                                                                     padding:0.1rem 0.4rem;white-space:nowrap;">
                                                                            ⛏ MIN/BTN Lv.{{ $gathering->gathering_level }}{{ str_repeat('★', $gathering->stars) }}
                                                                        </span>
                                                                    @endif
                                                                @elseif($mat->is_craftable)
                                                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;color:#f0c030;
                                                                                 border:1px solid rgba(240,192,48,0.2);background:rgba(40,30,0,0.4);
                                                                                 padding:0.1rem 0.4rem;white-space:nowrap;">⚒ craftável</span>
                                                                @else
                                                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;color:var(--dim);
                                                                                 border:1px solid rgba(74,84,112,0.3);background:rgba(10,10,25,0.4);
                                                                                 padding:0.1rem 0.4rem;white-space:nowrap;">🏪 mercado</span>
                                                                @endif
                                                            </td>
                                                            @if($serverId)
                                                            <td style="text-align:right;padding:0.45rem 0 0.45rem 0.5rem;">
                                                                @if($price && $price->minPriceNQ > 0)
                                                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.78rem;color:#ccd4f0;">
                                                                        {{ number_format($price->minPriceNQ) }}
                                                                        <span style="font-size:0.6rem;opacity:0.4;">g</span>
                                                                    </span>
                                                                @else
                                                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.7rem;color:#2a3060;">—</span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>

                                {{-- ══ SEÇÃO 2: USADO EM ══ --}}
                                @php $usedInGrouped = $usedIn->groupBy('item_id'); @endphp
                                @if($usedInGrouped->isNotEmpty())
                                    <div class="flex items-center gap-2 px-6 pt-2 pb-2"
                                         style="border-top:1px solid rgba(37,37,96,0.5);border-bottom:1px solid rgba(37,37,96,0.4);">
                                        <span style="font-family:'Cinzel',serif;font-size:0.48rem;letter-spacing:0.2em;
                                                     text-transform:uppercase;color:#2a3460;">USADO EM</span>
                                        <div style="height:1px;flex:1;background:rgba(37,37,96,0.5);"></div>
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;color:#2e3a60;">
                                            {{ $usedInGrouped->count() }} {{ $usedInGrouped->count() === 1 ? 'item' : 'itens' }}
                                        </span>
                                    </div>
                                    <div class="px-6 py-3">
                                        <div class="overflow-x-auto">
                                            <table style="width:100%;border-collapse:collapse;">
                                                <thead>
                                                    <tr>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:left;padding:0.3rem 0.5rem 0.3rem 0;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Item Craftado</th>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:left;padding:0.3rem 0.5rem;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Jobs</th>
                                                        <th style="font-family:'Cinzel',serif;font-size:0.46rem;letter-spacing:0.14em;
                                                                   text-transform:uppercase;color:#2a3460;font-weight:400;
                                                                   text-align:center;padding:0.3rem 0 0.3rem 0.5rem;width:3.5rem;
                                                                   border-bottom:1px solid rgba(37,37,96,0.4);">Nível</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($usedInGrouped->take(12) as $groupedItemId => $group)
                                                        @php
                                                            $usingItem = $group->first()->item;
                                                            $usingJobs = $group
                                                                ->map(fn($r) => \App\Enums\Job::tryFrom($r->job_id)?->getAbbreviation())
                                                                ->filter()
                                                                ->unique()
                                                                ->values();
                                                            $usingLevel = $group->first()->craft_level;
                                                        @endphp
                                                        <tr style="border-bottom:1px solid rgba(37,37,96,0.15);">
                                                            <td style="padding:0.4rem 0.5rem 0.4rem 0;">
                                                                <span style="font-size:0.78rem;color:#7a88a8;">
                                                                    {{ $usingItem->name ?? '—' }}
                                                                </span>
                                                            </td>
                                                            <td style="padding:0.4rem 0.5rem;">
                                                                <div class="flex flex-wrap gap-1">
                                                                    @forelse($usingJobs as $abbr)
                                                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;
                                                                                     color:#f0c030;border:1px solid rgba(240,192,48,0.2);
                                                                                     background:rgba(40,30,0,0.3);padding:0.05rem 0.35rem;">
                                                                            {{ $abbr }}
                                                                        </span>
                                                                    @empty
                                                                        <span style="color:#2a3060;font-size:0.65rem;">—</span>
                                                                    @endforelse
                                                                </div>
                                                            </td>
                                                            <td style="text-align:center;padding:0.4rem 0 0.4rem 0.5rem;">
                                                                <span style="font-family:'Share Tech Mono',monospace;font-size:0.75rem;color:#5569a0;">
                                                                    {{ $usingLevel }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @if($usedInGrouped->count() > 12)
                                                        <tr>
                                                            <td colspan="3" style="padding:0.4rem 0;">
                                                                <span style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;color:#2a3460;">
                                                                    + {{ $usedInGrouped->count() - 12 }} outros itens
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                            </td>
                        </tr>
                    </tbody>
                @endforeach

            </table>
        </div>

        {{-- Paginação --}}
        @if($recipes->hasPages())
            <div class="flex items-center justify-between px-5 py-3.5"
                 style="border-top:1px solid #252560;">
                <button wire:click="previousPage" @disabled($recipes->onFirstPage())
                        class="ff-btn-ghost" aria-label="Página anterior">
                    ◄ Anterior
                </button>
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:var(--dim);">
                    {{ $recipes->currentPage() }} / {{ $recipes->lastPage() }}
                </span>
                <button wire:click="nextPage" @disabled(!$recipes->hasMorePages())
                        class="ff-btn-ghost" aria-label="Próxima página">
                    Próxima ►
                </button>
            </div>
        @endif
    </div>

@else
    <div class="ff-box p-12 text-center">
        <div style="font-size:2.5rem;margin-bottom:1rem;opacity:0.35;" aria-hidden="true">◇</div>
        <p class="ff-label" style="color:#5a6080;font-size:0.7rem;">Nenhuma receita encontrada</p>
        <p style="font-size:0.75rem;color:var(--dim);margin-top:0.5rem;">
            Ajuste os filtros ou sincronize as receitas com
            <span style="font-family:'Share Tech Mono',monospace;color:#2a3870;">market:sync:items</span>.
        </p>
    </div>
@endif

</div>

</div>
