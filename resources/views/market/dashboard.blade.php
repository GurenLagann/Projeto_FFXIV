@extends('layouts.app')

@section('title', 'Market Dashboard — FFXIV Analyzer')

@section('content')

{{-- ── Page Header ── --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">
            ✦ &nbsp; EORZEA MARKET BOARD
        </p>
        <h1 class="ff-title text-3xl">Market Analyzer</h1>
        <p class="mt-1.5" style="font-size:0.8rem;color:var(--dim);">
            Lucratividade de crafting em tempo real via Universalis & XIVAPI.
        </p>
    </div>
    <a href="{{ route('market.history') }}"
       class="ff-btn-ghost self-start sm:self-auto" style="font-size:0.6rem;">
        Ver Histórico ►
    </a>
</div>

{{-- ── Stat Cards ── --}}
@php
    $bestProfit = $recentAnalyses->isNotEmpty() && $recentAnalyses->first()->results
        ? collect($recentAnalyses->first()->results)->max('profit') ?? 0
        : 0;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">

    {{-- Servers --}}
    <div class="ff-box ff-card-hover p-5">
        <div class="flex items-start justify-between mb-3">
            <p class="ff-label" style="font-size:0.55rem;color:#3a4a70;">Servidores</p>
            <span style="color:#4040a0;font-size:0.7rem;" aria-hidden="true">◆</span>
        </div>
        <p class="ff-num text-3xl font-bold" style="color:#5599ff;">{{ $servers->count() }}</p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#5599ff30,transparent);"></div>
    </div>

    {{-- Analyses --}}
    <div class="ff-box ff-card-hover p-5">
        <div class="flex items-start justify-between mb-3">
            <p class="ff-label" style="font-size:0.55rem;color:#3a4a70;">Análises</p>
            <span style="color:#a855f7;font-size:0.7rem;" aria-hidden="true">◆</span>
        </div>
        <p class="ff-num text-3xl font-bold" style="color:#a855f7;">{{ $recentAnalyses->count() }}</p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#a855f730,transparent);"></div>
    </div>

    {{-- Jobs --}}
    <div class="ff-box ff-card-hover p-5">
        <div class="flex items-start justify-between mb-3">
            <p class="ff-label" style="font-size:0.55rem;color:#3a4a70;">Jobs</p>
            <span style="color:#00dd77;font-size:0.7rem;" aria-hidden="true">◆</span>
        </div>
        <p class="ff-num text-3xl font-bold" style="color:#00dd77;">{{ count($jobs) - 1 }}</p>
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#00dd7730,transparent);"></div>
    </div>

    {{-- Best Profit --}}
    <div class="ff-box p-5" style="border-color:#3a3060;">
        <div class="flex items-start justify-between mb-3">
            <p class="ff-label" style="font-size:0.55rem;color:#4a3a18;">Melhor Lucro</p>
            <span style="color:#f0c030;font-size:0.7rem;" aria-hidden="true">◆</span>
        </div>
        <p class="ff-num font-bold truncate" style="font-size:1.7rem;color:#f0c030;">
            {{ $bestProfit > 0 ? number_format($bestProfit) : '——' }}
        </p>
        @if($bestProfit > 0)
            <p style="font-family:'Share Tech Mono',monospace;font-size:0.6rem;color:#7a5510;margin-top:2px;">gil</p>
        @endif
        <div class="mt-3 h-px" style="background:linear-gradient(90deg,#f0c03030,transparent);"></div>
    </div>
</div>

{{-- ── Profit Chart ── --}}
@if($chartLabels->isNotEmpty())
<div class="ff-box p-5 mb-8">
    <div class="flex items-center gap-3 mb-4">
        <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
        <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">
            Top 10 Itens — Última Análise
        </h2>
    </div>
    <div class="ff-divider mb-4"></div>
    <div style="position:relative;height:200px;">
        <canvas id="profitChart" aria-label="Gráfico de barras — top 10 itens mais lucrativos"></canvas>
    </div>
</div>
@endif

{{-- ── Market Analyzer (Livewire) ── --}}
<livewire:market-analyzer />

{{-- ── Recent Analyses ── --}}
@if($recentAnalyses->isNotEmpty())
<div class="mt-10">
    <div class="flex items-center gap-3 mb-3">
        <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
        <h2 class="ff-label" style="font-size:0.6rem;letter-spacing:0.2em;">Análises Recentes</h2>
    </div>

    <div class="ff-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Histórico de análises recentes">
                <thead>
                    <tr>
                        <th scope="col" class="text-left">Servidor</th>
                        <th scope="col" class="text-right">Oportunidades</th>
                        <th scope="col" class="text-right hidden sm:table-cell">Duração</th>
                        <th scope="col" class="text-right">Quando</th>
                        <th scope="col" class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAnalyses as $analysis)
                    <tr class="group">
                        <td class="font-medium" style="color:#ccd4f0;">
                            {{ $analysis->server->name ?? 'N/A' }}
                        </td>
                        <td class="text-right">
                            @if($analysis->total_opportunities > 0)
                                <span class="ff-badge-profit">{{ $analysis->total_opportunities }}</span>
                            @else
                                <span class="ff-badge-neutral">0</span>
                            @endif
                        </td>
                        <td class="text-right ff-num hidden sm:table-cell" style="color:var(--dim);font-size:0.75rem;">
                            {{ number_format($analysis->execution_time ?? 0) }} ms
                        </td>
                        <td class="text-right ff-num" style="color:var(--dim);font-size:0.75rem;">
                            {{ $analysis->created_at->diffForHumans() }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('market.analysis.show', $analysis) }}"
                               class="ff-btn-ghost opacity-0 group-hover:opacity-100"
                               style="padding:0.25rem 0.6rem;font-size:0.55rem;transition:opacity 120ms,border-color 120ms,color 120ms;">
                                Ver ►
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
{{-- Chart.js: loaded only on this page, not globally --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('profitChart');
    if (!canvas) return;

    const labels = @json($chartLabels ?? []);
    const data   = @json($chartData ?? []);

    const posColor = 'rgba(240,192,48,0.65)';
    const negColor = 'rgba(255,85,51,0.65)';
    const posBorder = '#f0c030';
    const negBorder = '#ff5533';

    window.profitChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Lucro (gil)',
                data,
                backgroundColor: data.map(v => v >= 0 ? posColor : negColor),
                borderColor: data.map(v => v >= 0 ? posBorder : negBorder),
                borderWidth: 1,
                borderRadius: 0,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0b0b1e',
                    borderColor: '#252560',
                    borderWidth: 1,
                    titleColor: '#8899cc',
                    titleFont: { family: 'Cinzel, serif', size: 10 },
                    bodyColor: '#f0c030',
                    bodyFont: { family: 'Share Tech Mono, monospace', size: 12 },
                    callbacks: {
                        label: ctx => `  ${ctx.parsed.y.toLocaleString()} g`
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#7280a0',
                        font: { size: 10 },
                        maxRotation: 30
                    },
                    grid: { color: 'rgba(37,37,96,0.4)' },
                    border: { color: '#252560' }
                },
                y: {
                    ticks: {
                        color: '#7280a0',
                        font: { family: 'Share Tech Mono, monospace', size: 10 },
                        callback: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M g'
                                     : v >= 1e3 ? (v/1e3).toFixed(0)+'k g'
                                     : v + ' g'
                    },
                    grid: { color: 'rgba(37,37,96,0.35)' },
                    border: { color: '#252560' }
                }
            }
        }
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('results-updated', ([payload]) => {
            if (!window.profitChart || !payload?.labels) return;
            window.profitChart.data.labels = payload.labels;
            window.profitChart.data.datasets[0].data = payload.data;
            window.profitChart.data.datasets[0].backgroundColor = payload.data.map(v => v >= 0 ? posColor : negColor);
            window.profitChart.data.datasets[0].borderColor = payload.data.map(v => v >= 0 ? posBorder : negBorder);
            window.profitChart.update('active');
        });
    });
});
</script>
@endpush
