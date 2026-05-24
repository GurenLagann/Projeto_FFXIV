@extends('layouts.app')

@section('title', 'Alertas de Preço — FFXIV Market')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; SISTEMA DE ALERTAS</p>
        <h1 class="ff-title text-2xl">Alertas de Preço</h1>
        <p class="mt-1" style="font-size:0.78rem;color:var(--dim);">
            Notificações automáticas quando oportunidades superam seus critérios.
        </p>
    </div>
    <a href="{{ route('alerts.create') }}" class="ff-btn-primary" style="font-size:0.65rem;">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Novo Alerta
    </a>
</div>

<div class="ff-box overflow-hidden">
    @if($alerts->isEmpty())
        <div class="p-14 text-center">
            <div style="font-size:3rem;margin-bottom:1rem;color:#252560;" aria-hidden="true">◇</div>
            <p class="ff-label" style="font-size:0.65rem;">Nenhum alerta configurado</p>
            <p style="font-size:0.75rem;color:var(--dim);margin-top:0.4rem;margin-bottom:1.2rem;">
                Configure alertas para ser notificado sobre oportunidades de lucro.
            </p>
            <a href="{{ route('alerts.create') }}" class="ff-btn-ghost" style="display:inline-flex;">
                Criar primeiro alerta ►
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="ff-table w-full" aria-label="Lista de alertas de preço">
                <thead>
                    <tr>
                        <th scope="col" class="text-left">Item</th>
                        <th scope="col" class="text-left hidden sm:table-cell">Servidor</th>
                        <th scope="col" class="text-right">Lucro Mín.</th>
                        <th scope="col" class="text-right hidden md:table-cell">Margem</th>
                        <th scope="col" class="text-right hidden lg:table-cell">Último Disparo</th>
                        <th scope="col" class="text-right">Status</th>
                        <th scope="col" class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alerts as $alert)
                    <tr class="group" x-data>
                        <td class="font-medium max-w-[200px]">
                            <div class="flex items-center gap-2"
                                 title="{{ $alert->item->name ?? "Item #{$alert->item_id}" }}">
                                @if($alert->item?->iconUrl)
                                    <img src="{{ $alert->item->iconUrl }}"
                                         alt="" width="20" height="20"
                                         style="flex-shrink:0;image-rendering:pixelated;opacity:0.9;"
                                         loading="lazy"
                                         onerror="this.style.display='none'">
                                @endif
                                <span class="block truncate" style="color:#ccd4f0;">
                                    {{ $alert->item->name ?? "Item #{$alert->item_id}" }}
                                </span>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell ff-num" style="color:var(--dim);font-size:0.8rem;">
                            {{ $alert->server->name ?? 'N/A' }}
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <span class="ff-num" style="color:#f0c030;font-weight:600;">{{ number_format($alert->min_profit) }}</span>
                            <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:var(--hi);" aria-hidden="true"> g</span>
                        </td>
                        <td class="text-right hidden md:table-cell ff-num" style="color:var(--dim);font-size:0.8rem;">
                            {{ $alert->min_margin }}%
                        </td>
                        <td class="text-right hidden lg:table-cell ff-num" style="color:var(--dim);font-size:0.72rem;">
                            {{ $alert->last_notified_at ? $alert->last_notified_at->diffForHumans() : '—' }}
                        </td>
                        <td class="text-right">
                            <form action="{{ route('alerts.toggle', $alert) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                {{-- ff-touch: min-height 44px so toggle is reachable on touch --}}
                                <button type="submit"
                                        aria-label="{{ $alert->is_active ? 'Desativar alerta' : 'Ativar alerta' }}"
                                        class="ff-touch {{ $alert->is_active ? 'ff-badge-profit' : 'ff-badge-neutral' }}"
                                        style="cursor:pointer;border:none;background:none;padding:0.25rem 0.6rem;
                                               font-family:'Share Tech Mono',monospace;font-size:0.7rem;">
                                    {{ $alert->is_active ? 'Ativo' : 'Inativo' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-right">
                            <form action="{{ route('alerts.destroy', $alert) }}" method="POST" class="inline"
                                  x-on:submit.prevent="if(confirm('Excluir este alerta?')) $el.submit()">
                                @csrf @method('DELETE')
                                {{-- Visible by default on mobile (touch has no hover); fades in on desktop hover --}}
                                <button type="submit"
                                        aria-label="Excluir alerta de {{ $alert->item->name ?? $alert->item_id }}"
                                        class="ff-touch ff-label sm:opacity-0 sm:group-hover:opacity-100 transition-opacity"
                                        style="font-size:0.6rem;color:var(--fire);background:none;border:none;
                                               cursor:pointer;transition:opacity 120ms;">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4" style="border-top:1px solid #252560;">
            {{ $alerts->links() }}
        </div>
    @endif
</div>

@endsection
