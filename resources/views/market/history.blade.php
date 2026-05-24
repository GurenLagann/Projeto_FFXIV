@extends('layouts.app')

@section('title', 'Histórico de Análises — FFXIV Market')

@section('content')

{{-- ── Header ── --}}
<div class="mb-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; REGISTROS DE BATALHA</p>
        <h1 class="ff-title text-2xl">Histórico de Análises</h1>
        <p class="mt-1.5" style="font-size:0.78rem;color:var(--dim);">
            {{ $analyses->total() }} análise{{ $analyses->total() !== 1 ? 's' : '' }} registrada{{ $analyses->total() !== 1 ? 's' : '' }}
        </p>
    </div>
    <a href="{{ route('market.dashboard') }}" class="ff-btn-ghost self-start sm:self-auto" style="font-size:0.6rem;">
        ◄ Dashboard
    </a>
</div>

<div class="ff-box overflow-hidden">

    @if($analyses->isEmpty())
        <div class="p-14 text-center">
            <div style="font-size:3rem;color:#252560;margin-bottom:1rem;" aria-hidden="true">◇</div>
            <p class="ff-label" style="font-size:0.65rem;">Nenhuma análise registrada</p>
            <p style="font-size:0.75rem;color:var(--dim);margin-top:0.4rem;margin-bottom:1.2rem;">
                Execute sua primeira análise no dashboard.
            </p>
            <a href="{{ route('market.dashboard') }}" class="ff-btn-ghost" style="display:inline-flex;">
                ► Ir ao Dashboard
            </a>
        </div>
    @else

        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Histórico de análises de mercado">
                <thead>
                    <tr>
                        <th scope="col" class="text-left" style="width:2.8rem;">#</th>
                        <th scope="col" class="text-left">Servidor</th>
                        <th scope="col" class="text-left hidden lg:table-cell">Filtros</th>
                        <th scope="col" class="text-right">Oport.</th>
                        <th scope="col" class="text-right hidden md:table-cell">Melhor Lucro</th>
                        <th scope="col" class="text-right hidden sm:table-cell">Duração</th>
                        <th scope="col" class="text-right hidden md:table-cell">Data</th>
                        <th scope="col" class="text-right" style="width:7rem;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                        @php
                            $filters    = $analysis->filters ?? [];
                            $results    = $analysis->results ?? [];
                            $bestProfit = collect($results)->max('profit') ?? 0;
                            $server     = $analysis->server;

                            $jobLabel = null;
                            if (!empty($filters['job_id'])) {
                                $jobEnum  = \App\Enums\Job::tryFrom((int) $filters['job_id']);
                                $jobLabel = $jobEnum?->getAbbreviation();
                            }

                            $minLv     = $filters['min_level'] ?? null;
                            $maxLv     = $filters['max_level'] ?? null;
                            $showLevel = ($minLv && $minLv > 1) || ($maxLv && $maxLv < 100);
                            $minProfit = $filters['min_profit'] ?? null;
                        @endphp
                        <tr>
                            {{-- ID --}}
                            <td class="ff-num" style="color:#2e3a60;font-size:0.7rem;">
                                {{ $analysis->id }}
                            </td>

                            {{-- Servidor --}}
                            <td>
                                <span class="font-medium" style="color:#ccd4f0;">
                                    {{ $server->name ?? 'N/A' }}
                                </span>
                                @if($server?->datacenter)
                                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.58rem;
                                                 color:#2e3a60;display:block;margin-top:1px;">
                                        {{ $server->datacenter }}
                                    </span>
                                @endif
                            </td>

                            {{-- Filtros --}}
                            <td class="hidden lg:table-cell">
                                <div class="flex flex-wrap gap-1 items-center">
                                    @if($jobLabel)
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;
                                                     color:#f0c030;border:1px solid rgba(240,192,48,0.25);
                                                     background:rgba(40,30,0,0.35);padding:0.1rem 0.4rem;">
                                            {{ $jobLabel }}
                                        </span>
                                    @else
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;
                                                     color:#3a4460;border:1px solid #1e1e40;padding:0.1rem 0.4rem;">
                                            ALL
                                        </span>
                                    @endif

                                    @if($showLevel)
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;
                                                     color:var(--dim);border:1px solid #1e1e40;padding:0.1rem 0.4rem;">
                                            Lv.{{ $minLv ?? 1 }}–{{ $maxLv ?? 100 }}
                                        </span>
                                    @endif

                                    @if($minProfit && $minProfit > 0)
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;
                                                     color:#00aa55;border:1px solid rgba(0,170,85,0.2);
                                                     padding:0.1rem 0.4rem;">
                                            ≥{{ number_format($minProfit) }}g
                                        </span>
                                    @endif

                                    @if(!empty($filters['gatherable_only']))
                                        <span style="font-family:'Share Tech Mono',monospace;font-size:0.52rem;
                                                     color:#3a6040;border:1px solid rgba(0,100,50,0.2);
                                                     padding:0.1rem 0.4rem;" title="Apenas coletáveis">
                                            ⛏
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Oportunidades --}}
                            <td class="text-right whitespace-nowrap">
                                @if($analysis->total_opportunities > 0)
                                    <span class="ff-badge-profit">{{ $analysis->total_opportunities }}</span>
                                @else
                                    <span class="ff-badge-neutral">0</span>
                                @endif
                            </td>

                            {{-- Melhor Lucro --}}
                            <td class="text-right hidden md:table-cell whitespace-nowrap">
                                @if($bestProfit > 0)
                                    <span class="ff-num" style="font-size:0.82rem;color:#00dd77;font-weight:600;">
                                        +{{ number_format($bestProfit) }}
                                        <span style="font-size:0.6rem;opacity:0.45;font-weight:400;">g</span>
                                    </span>
                                @else
                                    <span style="color:#2e3a60;font-size:0.75rem;">—</span>
                                @endif
                            </td>

                            {{-- Duração --}}
                            <td class="text-right hidden sm:table-cell whitespace-nowrap ff-num"
                                style="color:var(--dim);font-size:0.72rem;">
                                @if($analysis->execution_time)
                                    @if($analysis->execution_time >= 1000)
                                        {{ number_format($analysis->execution_time / 1000, 1) }}s
                                    @else
                                        {{ $analysis->execution_time }}ms
                                    @endif
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Data --}}
                            <td class="text-right hidden md:table-cell whitespace-nowrap"
                                title="{{ $analysis->created_at->format('d/m/Y H:i:s') }}">
                                <span class="ff-num" style="color:var(--dim);font-size:0.72rem;">
                                    {{ $analysis->created_at->diffForHumans() }}
                                </span>
                            </td>

                            {{-- Ações --}}
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('market.analysis.show', $analysis) }}"
                                       class="ff-btn-ghost" style="padding:0.22rem 0.65rem;font-size:0.55rem;">
                                        Ver ►
                                    </a>
                                    <a href="{{ route('market.analysis.export', $analysis) }}"
                                       title="Exportar CSV"
                                       style="font-family:'Cinzel',serif;font-size:0.52rem;letter-spacing:0.1em;
                                              text-transform:uppercase;color:#3a4460;border:1px solid #1e1e40;
                                              padding:0.22rem 0.55rem;text-decoration:none;
                                              transition:color 120ms,border-color 120ms;"
                                       onmouseover="this.style.color='#5599ff';this.style.borderColor='#3a4a80'"
                                       onmouseout="this.style.color='#3a4460';this.style.borderColor='#1e1e40'">
                                        CSV
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($analyses->hasPages())
            <div class="flex items-center justify-between px-5 py-3.5"
                 style="border-top:1px solid #252560;">
                @if($analyses->onFirstPage())
                    <button class="ff-btn-ghost ff-touch" disabled aria-label="Página anterior">◄ Anterior</button>
                @else
                    <a href="{{ $analyses->previousPageUrl() }}" class="ff-btn-ghost ff-touch" aria-label="Página anterior">
                        ◄ Anterior
                    </a>
                @endif

                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:var(--dim);">
                    {{ $analyses->currentPage() }} / {{ $analyses->lastPage() }}
                </span>

                @if($analyses->hasMorePages())
                    <a href="{{ $analyses->nextPageUrl() }}" class="ff-btn-ghost ff-touch" aria-label="Próxima página">
                        Próxima ►
                    </a>
                @else
                    <button class="ff-btn-ghost ff-touch" disabled aria-label="Próxima página">Próxima ►</button>
                @endif
            </div>
        @endif

    @endif
</div>

@endsection
