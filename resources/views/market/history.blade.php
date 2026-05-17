@extends('layouts.app')

@section('title', 'Histórico de Análises — FFXIV Market')

@section('content')

<div class="mb-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; REGISTROS DE BATALHA</p>
        <h1 class="ff-title text-2xl">Histórico de Análises</h1>
        <p class="mt-1.5" style="font-size:0.78rem;color:#4a5470;">
            Todas as análises de mercado executadas.
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
            <p class="ff-label" style="color:#3a4060;font-size:0.65rem;">Nenhuma análise registrada</p>
            <p style="font-size:0.75rem;color:#2a3050;margin-top:0.4rem;margin-bottom:1.2rem;">
                Execute sua primeira análise no dashboard.
            </p>
            <a href="{{ route('market.dashboard') }}" class="ff-btn-ghost" style="display:inline-flex;">
                ► Ir ao Dashboard
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Histórico de análises">
                <thead>
                    <tr>
                        <th scope="col" class="text-left" style="width:3rem;">#</th>
                        <th scope="col" class="text-left">Servidor</th>
                        <th scope="col" class="text-right">Oportunidades</th>
                        <th scope="col" class="text-right hidden sm:table-cell">Duração</th>
                        <th scope="col" class="text-right hidden md:table-cell">Data</th>
                        <th scope="col" class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                    <tr class="group">
                        <td class="ff-num" style="color:#2e3a60;font-size:0.72rem;">
                            {{ $analysis->id }}
                        </td>
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
                        <td class="text-right hidden sm:table-cell ff-num" style="color:#3a4060;font-size:0.72rem;">
                            {{ $analysis->execution_time ? number_format($analysis->execution_time).' ms' : '—' }}
                        </td>
                        <td class="text-right hidden md:table-cell ff-num" style="color:#4a5470;font-size:0.72rem;">
                            {{ $analysis->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('market.analysis.show', $analysis) }}"
                                   class="ff-btn-ghost" style="padding:0.22rem 0.6rem;font-size:0.55rem;">
                                    Ver ►
                                </a>
                                <a href="{{ route('market.analysis.export', $analysis) }}"
                                   class="ff-label" style="font-size:0.55rem;color:#4040a0;text-decoration:none;transition:color 120ms;"
                                   onmouseover="this.style.color='#5599ff'" onmouseout="this.style.color='#4040a0'">
                                    CSV
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4" style="border-top:1px solid #252560;">
            {{ $analyses->links() }}
        </div>
    @endif
</div>

@endsection
