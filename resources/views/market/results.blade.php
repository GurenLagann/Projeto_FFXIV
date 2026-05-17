@extends('layouts.app')

@section('title', 'Resultados — ' . $server->name . ' — FFXIV Market')

@section('content')

{{-- ── Header ── --}}
<div class="mb-7 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; RESULTADO DA ANÁLISE</p>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="ff-title text-2xl">{{ $server->name }}</h1>
            @if($analysis->total_opportunities > 0)
                <span class="ff-badge-profit" style="font-size:0.7rem;">
                    {{ $analysis->total_opportunities }} oportunidades
                </span>
            @endif
        </div>
        <p class="mt-1.5" style="font-size:0.78rem;color:#4a5470;">
            <span class="ff-num">{{ $elapsed }}ms</span>
            <span style="color:#252560;margin:0 0.5rem;">◆</span>
            {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <a href="{{ route('market.analysis.export', $analysis) }}" class="ff-btn-ghost" style="font-size:0.6rem;">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exportar CSV
        </a>
        <a href="{{ route('market.dashboard') }}" class="ff-btn-ghost" style="font-size:0.6rem;">
            ◄ Dashboard
        </a>
    </div>
</div>

{{-- ── Stat Cards ── --}}
<div class="grid grid-cols-3 gap-3 mb-7">
    <div class="ff-box ff-card-hover p-5">
        <p class="ff-label mb-3" style="font-size:0.55rem;color:#2e4a30;">Oportunidades</p>
        <p class="ff-num font-bold" style="font-size:1.9rem;color:#00dd77;">
            {{ $results->total() }}
        </p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#00dd7730,transparent);"></div>
    </div>
    <div class="ff-box ff-card-hover p-5">
        <p class="ff-label mb-3" style="font-size:0.55rem;color:#1a2a5a;">Duração</p>
        <p class="ff-num font-bold" style="font-size:1.9rem;color:#5599ff;">
            {{ number_format($elapsed) }}<span style="font-size:0.9rem;color:#3a4060;margin-left:2px;">ms</span>
        </p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#5599ff30,transparent);"></div>
    </div>
    <div class="ff-box p-5" style="border-color:#3a3060;">
        <p class="ff-label mb-3" style="font-size:0.55rem;color:#4a3a18;">Melhor Lucro</p>
        <p class="ff-num font-bold truncate" style="font-size:1.75rem;color:#f0c030;">
            {{ number_format(collect($results->items())->max('profit') ?? 0) }}
            <span style="font-size:0.8rem;color:#7a5510;margin-left:2px;">g</span>
        </p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#f0c03030,transparent);"></div>
    </div>
</div>

{{-- ── Results Table ── --}}
<div class="ff-box overflow-hidden">

    <div class="flex items-center justify-between px-5 py-3.5" style="border-bottom:1px solid #252560;">
        <div class="flex items-center gap-3">
            <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Resultados</h2>
            <span class="ff-badge-neutral" style="font-size:0.65rem;">{{ $results->total() }}</span>
        </div>
        @if($results->hasPages())
            <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a4060;">
                {{ $results->currentPage() }} / {{ $results->lastPage() }}
            </span>
        @endif
    </div>

    @if($results->isEmpty())
        <div class="p-12 text-center">
            <div style="font-size:2.5rem;color:#252560;margin-bottom:1rem;" aria-hidden="true">◇</div>
            <p class="ff-label" style="color:#3a4060;font-size:0.65rem;">Nenhuma oportunidade encontrada</p>
            <p style="font-size:0.75rem;color:#2a3050;margin-top:0.4rem;">
                Tente reduzir o lucro mínimo ou remover o filtro de vendas/semana.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Resultados da análise para {{ $server->name }}">
                <thead>
                    <tr>
                        <th scope="col" class="text-left" style="width:2.5rem;">#</th>
                        <th scope="col" class="text-left">Item</th>
                        <th scope="col" class="text-right">Lucro</th>
                        <th scope="col" class="text-right hidden md:table-cell">Custo</th>
                        <th scope="col" class="text-right hidden md:table-cell">Receita</th>
                        <th scope="col" class="text-right">Margem</th>
                        <th scope="col" class="text-right hidden sm:table-cell">Vnd/Sem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $i => $r)
                        @php $profitable = $r['profit'] > 0; @endphp
                        <tr>
                            <td class="ff-num" style="color:#2e3a60;font-size:0.7rem;">
                                {{ ($results->currentPage() - 1) * $results->perPage() + $i + 1 }}
                            </td>
                            <td class="max-w-[240px]">
                                <div class="flex items-center gap-2" title="{{ $r['itemName'] }}">
                                    @if(!empty($r['itemIcon']))
                                        <img src="https://xivapi.com{{ $r['itemIcon'] }}"
                                             alt="" width="20" height="20"
                                             style="flex-shrink:0;image-rendering:pixelated;opacity:0.9;"
                                             loading="lazy">
                                    @endif
                                    <span class="block truncate font-medium" style="color:#ccd4f0;">
                                        {{ $r['itemName'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <span class="ff-num font-bold"
                                      style="color:{{ $profitable ? '#00dd77' : '#ff5533' }};">
                                    {{ $profitable ? '+' : '' }}{{ number_format($r['profit']) }}
                                    <span style="font-size:0.65rem;opacity:0.5;font-weight:400;">g</span>
                                </span>
                            </td>
                            <td class="text-right hidden md:table-cell whitespace-nowrap ff-num"
                                style="color:#3a4060;font-size:0.78rem;">
                                {{ number_format($r['costEstimate']) }}
                            </td>
                            <td class="text-right hidden md:table-cell whitespace-nowrap ff-num"
                                style="color:#3a4060;font-size:0.78rem;">
                                {{ number_format($r['revenueEstimate']) }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if($r['marginPercent'] > 20)
                                    <span class="ff-badge-profit">{{ number_format($r['marginPercent'], 1) }}%</span>
                                @elseif($r['marginPercent'] > 0)
                                    <span class="ff-badge-warn">{{ number_format($r['marginPercent'], 1) }}%</span>
                                @else
                                    <span class="ff-badge-loss">{{ number_format($r['marginPercent'], 1) }}%</span>
                                @endif
                            </td>
                            <td class="text-right hidden sm:table-cell whitespace-nowrap ff-num"
                                style="color:#3a4060;font-size:0.75rem;">
                                {{ number_format($r['salesPerWeek'], 1) }}/sem
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
            <div class="flex items-center justify-between px-5 py-3.5" style="border-top:1px solid #252560;">
                @if($results->onFirstPage())
                    <span class="ff-btn-ghost opacity-30 cursor-not-allowed" style="font-size:0.62rem;">◄ Anterior</span>
                @else
                    <a href="{{ $results->previousPageUrl() }}" class="ff-btn-ghost" style="font-size:0.62rem;">◄ Anterior</a>
                @endif

                <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#3a4060;">
                    {{ $results->currentPage() }} / {{ $results->lastPage() }}
                </span>

                @if($results->hasMorePages())
                    <a href="{{ $results->nextPageUrl() }}" class="ff-btn-ghost" style="font-size:0.62rem;">Próxima ►</a>
                @else
                    <span class="ff-btn-ghost opacity-30 cursor-not-allowed" style="font-size:0.62rem;">Próxima ►</span>
                @endif
            </div>
        @endif
    @endif
</div>

@endsection
