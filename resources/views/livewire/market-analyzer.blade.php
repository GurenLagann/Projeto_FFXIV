<div>

{{-- ══════════════════════════════════════════════════════
     CARD DE FILTROS
     ══════════════════════════════════════════════════════ --}}
<div class="ff-box mb-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between px-6 pt-5 pb-4">
        <div class="flex items-center gap-2">
            <span style="color:#4040a0;font-size:0.55rem;" aria-hidden="true">◆</span>
            <span class="ff-label" style="font-size:0.6rem;color:#3a4870;letter-spacing:0.2em;">FILTROS DE ANÁLISE</span>
        </div>
        @if($characterVerified)
            <div class="flex items-center gap-1.5">
                <span style="width:6px;height:6px;border-radius:50%;background:#00dd77;box-shadow:0 0 5px rgba(0,221,119,0.7);flex-shrink:0;" aria-hidden="true"></span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.62rem;color:#00aa55;">
                    {{ $characterName }}
                </span>
            </div>
        @endif
    </div>

    {{-- ── Character Banner ── --}}
    @if($characterVerified)
    <div class="mx-6 mb-4"
         style="background:linear-gradient(90deg,rgba(0,221,119,0.05) 0%,rgba(0,100,50,0.03) 100%);
                border:1px solid rgba(0,221,119,0.18);
                padding:0.65rem 1rem;">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                @if($characterAvatar)
                    <div style="position:relative;flex-shrink:0;">
                        <img src="{{ $characterAvatar }}" alt="{{ $characterName }}"
                             style="width:38px;height:38px;object-fit:cover;border:1px solid rgba(0,221,119,0.35);">
                        <span style="position:absolute;bottom:-3px;right:-3px;width:10px;height:10px;
                                     background:#00dd77;border:2px solid #06060f;border-radius:50%;"
                              aria-hidden="true"></span>
                    </div>
                @endif
                <div>
                    <p style="font-family:'Cinzel',serif;font-size:0.82rem;color:#ccd4f0;letter-spacing:0.03em;line-height:1.3;">
                        {{ $characterName }}
                    </p>
                    <p style="font-family:'Share Tech Mono',monospace;font-size:0.6rem;color:#00aa55;margin-top:1px;">
                        ✦&nbsp;{{ $characterServer }}
                        @if($jobId && isset($characterJobLevels[$jobId]) && $characterJobLevels[$jobId] > 0)
                            <span style="color:#3a5a3a;margin:0 0.3rem;">·</span>
                            <span style="color:#00cc66;">
                                Lv {{ $characterJobLevels[$jobId] }} {{ \App\Enums\Job::from($jobId)->getAbbreviation() }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
            <button wire:click="applyCharacterFilter" type="button"
                    style="font-family:'Cinzel',serif;font-size:0.55rem;letter-spacing:0.06em;
                           text-transform:uppercase;white-space:nowrap;
                           color:#00dd77;border:1px solid rgba(0,221,119,0.3);
                           background:rgba(0,221,119,0.06);
                           padding:0.35rem 0.75rem;cursor:pointer;
                           transition:border-color 120ms,background 120ms;"
                    onmouseover="this.style.borderColor='rgba(0,221,119,0.55)';this.style.background='rgba(0,221,119,0.13)'"
                    onmouseout="this.style.borderColor='rgba(0,221,119,0.3)';this.style.background='rgba(0,221,119,0.06)'">
                ⟶ Preencher
            </button>
        </div>
    </div>
    @endif

    <div class="ff-divider mx-6"></div>

    {{-- ── Error ── --}}
    @if($error)
        <div role="alert" aria-live="assertive"
             class="mx-6 mt-4 flex items-start gap-3 ff-alert-error px-4 py-3"
             style="font-size:0.8rem;">
            <span class="mt-0.5 flex-shrink-0" aria-hidden="true">✖</span>
            <span>{{ $error }}</span>
        </div>
    @endif

    {{-- ── Servidor + Job ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 px-6 pt-5 pb-0">

        {{-- Servidor --}}
        <div>
            <div class="flex items-center gap-2 mb-2.5">
                <div style="height:1px;width:10px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.5rem;color:#3a4870;letter-spacing:0.2em;">SERVIDOR</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <select wire:model="serverId"
                    style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                           padding:0.55rem 0.8rem;font-size:0.875rem;width:100%;border-radius:0;">
                <option value="">── selecione ──</option>
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
            @error('serverId')
                <p class="ff-error mt-1" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Job Grid --}}
        <div>
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
                    <span style="font-size:0.95rem;line-height:1;
                                 color:{{ $jobId === null ? '#f0c030' : '#2a3050' }};">✦</span>
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.46rem;
                                 color:{{ $jobId === null ? '#f0c030' : '#3a4460' }};">ALL</span>
                </button>

                {{-- Individual jobs --}}
                @foreach($jobs as $job)
                    @if($job->value !== 0)
                    @php $charLv = $characterJobLevels[$job->value] ?? 0; @endphp
                    <button type="button"
                            wire:click="$set('jobId', {{ $job->value }})"
                            title="{{ $job->getLabel() }}{{ $charLv ? ' · Lv '.$charLv : '' }}"
                            style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                                   gap:2px;padding:0.4rem 0.1rem;border:1px solid;cursor:pointer;
                                   border-color:{{ $jobId === $job->value ? '#f0c030' : '#1e1e40' }};
                                   background:{{ $jobId === $job->value ? 'rgba(240,192,48,0.09)' : 'rgba(4,4,14,0.8)' }};
                                   transition:border-color 100ms,background 100ms;">
                        <img src="{{ $job->getIconUrl() }}" alt="{{ $job->getAbbreviation() }}"
                             width="22" height="22"
                             style="image-rendering:pixelated;
                                    opacity:{{ $jobId === $job->value ? '1' : ($charLv ? '0.6' : '0.35') }};
                                    transition:opacity 100ms;">
                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.44rem;line-height:1;
                                     color:{{ $jobId === $job->value ? '#f0c030' : '#3a4460' }};">
                            {{ $job->getAbbreviation() }}
                        </span>
                        @if($charLv)
                            <span style="font-family:'Share Tech Mono',monospace;font-size:0.4rem;line-height:1;
                                         color:{{ $jobId === $job->value ? '#00cc66' : '#1a3a28' }};">
                                {{ $charLv }}
                            </span>
                        @endif
                    </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Filtros numéricos ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 px-6 py-5">

        {{-- Nível --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">NÍVEL</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <div class="flex items-center gap-1.5">
                <input type="number" wire:model="minLevel" min="1" max="100"
                       aria-label="Nível mínimo" placeholder="1"
                       style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                              padding:0.42rem 0.4rem;font-size:0.82rem;border-radius:0;width:100%;
                              text-align:center;font-family:'Share Tech Mono',monospace;">
                <span style="color:#252560;font-size:0.55rem;flex-shrink:0;">–</span>
                <input type="number" wire:model="maxLevel" min="1" max="100"
                       aria-label="Nível máximo" placeholder="100"
                       style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                              padding:0.42rem 0.4rem;font-size:0.82rem;border-radius:0;width:100%;
                              text-align:center;font-family:'Share Tech Mono',monospace;">
            </div>
        </div>

        {{-- Lucro mínimo --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">
                    LUCRO MÍN <span style="color:#2a2a50;">(g)</span>
                </span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <input type="number" wire:model="minProfit" min="0" step="1000"
                   placeholder="5000"
                   style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                          padding:0.42rem 0.6rem;font-size:0.82rem;border-radius:0;width:100%;
                          font-family:'Share Tech Mono',monospace;">
        </div>

        {{-- Margem mínima --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">
                    MARGEM <span style="color:#2a2a50;">(%)</span>
                </span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <input type="number" wire:model="minMargin" min="0" max="100" step="5"
                   placeholder="20"
                   style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                          padding:0.42rem 0.6rem;font-size:0.82rem;border-radius:0;width:100%;
                          font-family:'Share Tech Mono',monospace;">
        </div>

        {{-- Vendas/semana --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">VENDAS/SEM</span>
                <div style="height:1px;flex:1;background:#252560;"></div>
            </div>
            <input type="number" wire:model="minSales" min="0" step="1"
                   placeholder="5"
                   style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                          padding:0.42rem 0.6rem;font-size:0.82rem;border-radius:0;width:100%;
                          font-family:'Share Tech Mono',monospace;">
        </div>
    </div>

    {{-- ── Métricas avançadas (colapsável) ── --}}
    <div x-data="{ open: false }" style="border-top:1px solid #1a1a38;">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-2.5"
                style="background:none;border:none;cursor:pointer;">
            <div class="flex items-center gap-2">
                <span style="font-size:0.55rem;color:#2a3050;transition:transform 200ms;"
                      :style="open ? 'transform:rotate(90deg)' : ''">▸</span>
                <span class="ff-label" style="font-size:0.48rem;color:#2a3870;letter-spacing:0.18em;">
                    MÉTRICAS AVANÇADAS
                </span>
            </div>
            <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;color:#252560;"
                  x-text="open ? '▲' : '▼'"></span>
        </button>
        <div x-show="open" style="display:none;"
             class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-6 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                    <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">CUSTO DOS MATERIAIS</span>
                    <div style="height:1px;flex:1;background:#252560;"></div>
                </div>
                <select wire:model="costMetric"
                        style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                               padding:0.42rem 0.6rem;font-size:0.82rem;width:100%;border-radius:0;">
                    @foreach($costMetrics as $metric)
                        <option value="{{ $metric->value }}">{{ $metric->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div style="height:1px;width:8px;background:linear-gradient(90deg,transparent,#252560);"></div>
                    <span class="ff-label" style="font-size:0.48rem;color:#3a4870;letter-spacing:0.18em;">RECEITA DO ITEM</span>
                    <div style="height:1px;flex:1;background:#252560;"></div>
                </div>
                <select wire:model="revMetric"
                        style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;
                               padding:0.42rem 0.6rem;font-size:0.82rem;width:100%;border-radius:0;">
                    @foreach($revMetrics as $metric)
                        <option value="{{ $metric->value }}">{{ $metric->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Toggle: apenas coletáveis ── --}}
    <div class="flex items-center gap-3 px-6 pb-4">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;user-select:none;">
            <input type="checkbox" wire:model="gatherableOnly"
                   style="accent-color:#00dd77;width:14px;height:14px;cursor:pointer;">
            <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a5a3a;letter-spacing:0.04em;">
                ⛏ Apenas receitas com materiais coletáveis
            </span>
        </label>
    </div>

    {{-- ── Actions ── --}}
    <div class="flex items-center gap-5 px-6 py-4" style="border-top:1px solid #1e1e3a;">
        <button wire:click="runAnalysis"
                wire:loading.attr="disabled"
                class="ff-btn-primary">
            <svg wire:loading.remove class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span wire:loading.remove>Analisar Mercado</span>
            <span wire:loading>Aguardando...</span>
        </button>

        @if($analyzed)
            <p aria-live="polite"
               style="font-family:'Share Tech Mono',monospace;font-size:0.72rem;color:#3a4460;">
                <span style="color:#ccd4f0;font-weight:600;">{{ count($results) }}</span>
                <span style="margin-left:0.2rem;">oportunidades</span>
            </p>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     ATB LOADING
     ══════════════════════════════════════════════════════ --}}
<div wire:loading wire:target="runAnalysis"
     class="ff-box mb-4 p-5"
     aria-label="Analisando mercado..." role="status">
    <div class="flex items-center gap-3 mb-3">
        <span style="color:#5599ff;font-size:0.6rem;letter-spacing:0.15em;
                     font-family:'Cinzel',serif;text-transform:uppercase;">
            ► Processando dados de mercado...
        </span>
    </div>
    <div class="atb-track">
        <div class="atb-fill"></div>
    </div>
    <div class="mt-4 space-y-2.5">
        @for($i = 0; $i < 6; $i++)
            <div class="flex items-center gap-4">
                <div class="ff-skeleton" style="width:20px;height:20px;flex-shrink:0;"></div>
                <div class="ff-skeleton h-3 flex-1 max-w-[200px]"></div>
                <div class="ff-skeleton h-3 w-16 ml-auto"></div>
                <div class="ff-skeleton h-3 w-16"></div>
                <div class="ff-skeleton h-3 w-12"></div>
                <div class="ff-skeleton h-3 w-10"></div>
            </div>
        @endfor
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     RESULTADOS
     ══════════════════════════════════════════════════════ --}}
@if($analyzed && !empty($results))
    <div class="ff-box overflow-hidden" wire:loading.remove wire:target="runAnalysis">

        <div class="flex items-center justify-between px-5 py-3.5"
             style="border-bottom:1px solid #252560;">
            <div class="flex items-center gap-3">
                <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
                <h3 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Resultados</h3>
                <span class="ff-badge-neutral" style="font-size:0.65rem;">{{ $pagedResults->total() }}</span>
            </div>
            @if($pagedResults->hasPages())
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a4060;">
                    {{ $pagedResults->currentPage() }} / {{ $pagedResults->lastPage() }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Resultados de análise de mercado">
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
                            wire:click="sortBy('itemName')"
                            style="{{ $sortColumn === 'itemName' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center gap-1">
                                Item
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'itemName' ? '1' : '0.35' }};">{{ $sortIcon('itemName') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-left hidden lg:table-cell" style="width:5rem;">Jobs</th>
                        <th scope="col" class="text-right cursor-pointer select-none"
                            wire:click="sortBy('profit')"
                            style="{{ $sortColumn === 'profit' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Lucro
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'profit' ? '1' : '0.35' }};">{{ $sortIcon('profit') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-right hidden md:table-cell cursor-pointer select-none"
                            wire:click="sortBy('costEstimate')"
                            style="{{ $sortColumn === 'costEstimate' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Custo
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'costEstimate' ? '1' : '0.35' }};">{{ $sortIcon('costEstimate') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-right hidden md:table-cell cursor-pointer select-none"
                            wire:click="sortBy('revenueEstimate')"
                            style="{{ $sortColumn === 'revenueEstimate' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Receita
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'revenueEstimate' ? '1' : '0.35' }};">{{ $sortIcon('revenueEstimate') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-right cursor-pointer select-none"
                            wire:click="sortBy('marginPercent')"
                            style="{{ $sortColumn === 'marginPercent' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Margem
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'marginPercent' ? '1' : '0.35' }};">{{ $sortIcon('marginPercent') }}</span>
                            </span>
                        </th>
                        <th scope="col" class="text-right hidden sm:table-cell cursor-pointer select-none"
                            wire:click="sortBy('salesPerWeek')"
                            style="{{ $sortColumn === 'salesPerWeek' ? $thActive : $thBase }}">
                            <span class="inline-flex items-center justify-end gap-1">
                                Vnd/Sem
                                <span style="font-size:0.55rem;opacity:{{ $sortColumn === 'salesPerWeek' ? '1' : '0.35' }};">{{ $sortIcon('salesPerWeek') }}</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagedResults as $result)
                        @php
                            $profitable = $result['profit'] > 0;
                            $highMargin = $result['marginPercent'] > 20;
                        @endphp
                        <tr>
                            <td class="font-medium max-w-[200px]">
                                <div class="flex items-center gap-2" title="{{ $result['itemName'] }}">
                                    @if(!empty($result['itemIcon']))
                                        <img src="https://xivapi.com{{ $result['itemIcon'] }}"
                                             alt="" width="20" height="20"
                                             style="flex-shrink:0;image-rendering:pixelated;opacity:0.9;"
                                             loading="lazy">
                                    @endif
                                    <div class="min-w-0">
                                        <span class="block truncate" style="color:#ccd4f0;">
                                            {{ $result['itemName'] }}
                                        </span>
                                        @if(!empty($result['allMatsGatherable']))
                                            <span style="font-family:'Share Tech Mono',monospace;font-size:0.45rem;
                                                         color:#00aa55;letter-spacing:0.05em;">
                                                ⛏ mats coletáveis
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="hidden lg:table-cell">
                                @if(!empty($result['craftJobs']))
                                    <div class="flex flex-wrap gap-0.5">
                                        @foreach($result['craftJobs'] as $job)
                                            <span style="font-family:'Share Tech Mono',monospace;
                                                         font-size:0.42rem;letter-spacing:0.03em;
                                                         color:#4a5090;border:1px solid #252560;
                                                         padding:1px 3px;white-space:nowrap;">
                                                {{ $job }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <span class="ff-num"
                                      style="font-weight:600;color:{{ $profitable ? '#00dd77' : '#ff5533' }};">
                                    {{ $profitable ? '+' : '' }}{{ number_format($result['profit']) }}
                                    <span style="font-size:0.65rem;opacity:0.5;font-weight:400;">g</span>
                                </span>
                            </td>
                            <td class="text-right hidden md:table-cell whitespace-nowrap ff-num"
                                style="color:#4a5470;font-size:0.78rem;">
                                {{ number_format($result['costEstimate']) }}
                            </td>
                            <td class="text-right hidden md:table-cell whitespace-nowrap ff-num"
                                style="color:#4a5470;font-size:0.78rem;">
                                {{ number_format($result['revenueEstimate']) }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if($highMargin)
                                    <span class="ff-badge-profit">{{ number_format($result['marginPercent'], 1) }}%</span>
                                @elseif($result['marginPercent'] > 0)
                                    <span class="ff-badge-warn">{{ number_format($result['marginPercent'], 1) }}%</span>
                                @else
                                    <span class="ff-badge-loss">{{ number_format($result['marginPercent'], 1) }}%</span>
                                @endif
                            </td>
                            <td class="text-right hidden sm:table-cell whitespace-nowrap ff-num"
                                style="color:#4a5470;font-size:0.75rem;">
                                {{ number_format($result['salesPerWeek'], 1) }}/sem
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pagedResults->hasPages())
            <div class="flex items-center justify-between px-5 py-3.5"
                 style="border-top:1px solid #252560;">
                <button wire:click="prevPage" @disabled($pagedResults->onFirstPage())
                        class="ff-btn-ghost" aria-label="Página anterior">
                    ◄ Anterior
                </button>
                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a4060;">
                    {{ $pagedResults->currentPage() }} / {{ $pagedResults->lastPage() }}
                </span>
                <button wire:click="nextPage" @disabled(!$pagedResults->hasMorePages())
                        class="ff-btn-ghost" aria-label="Próxima página">
                    Próxima ►
                </button>
            </div>
        @endif
    </div>

@elseif($analyzed && empty($results))
    <div class="ff-box p-12 text-center" wire:loading.remove wire:target="runAnalysis">
        <div style="font-size:2.5rem;margin-bottom:1rem;opacity:0.35;" aria-hidden="true">◇</div>
        <p class="ff-label" style="color:#5a6080;font-size:0.7rem;">Nenhuma oportunidade encontrada</p>
        <p style="font-size:0.75rem;color:#3a4060;margin-top:0.5rem;">
            Tente reduzir o lucro mínimo ou remover o filtro de vendas/semana.
        </p>
    </div>
@endif

</div>
