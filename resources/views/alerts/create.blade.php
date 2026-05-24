@extends('layouts.app')

@section('title', 'Novo Alerta — FFXIV Market')

@section('content')

<div class="mb-7 flex items-center justify-between">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; SISTEMA DE ALERTAS</p>
        <h1 class="ff-title text-2xl">Novo Alerta de Preço</h1>
    </div>
    <a href="{{ route('alerts.index') }}" class="ff-btn-ghost" style="font-size:0.6rem;">
        ◄ Meus Alertas
    </a>
</div>

<div class="max-w-xl">
    <div class="ff-box p-6">

        <div class="flex items-center gap-3 mb-4">
            <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Configurar Alerta</h2>
        </div>
        <div class="ff-divider mb-6"></div>

        @php $sel = 'background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;padding:0.55rem 0.8rem;font-size:0.875rem;border-radius:0;width:100%;'; @endphp

        <form method="POST" action="{{ route('alerts.store') }}" class="space-y-5">
            @csrf

            {{-- Item --}}
            <div x-data="{
                    iconUrl: '{{ old('item_id') ? ($items->firstWhere('id', old('item_id'))?->iconUrl ?? '') : '' }}',
                    onChange(e) {
                        const opt = e.target.selectedOptions[0];
                        const url = opt ? opt.dataset.icon : '';
                        this.iconUrl = url || '';
                    }
                }">
                <label for="item_id" class="ff-label">
                    Item <span style="color:#ff5533;" aria-label="obrigatório">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <div style="width:32px;height:32px;flex-shrink:0;background:rgba(4,4,14,0.8);border:1px solid #252560;display:flex;align-items:center;justify-content:center;">
                        <img x-show="iconUrl" :src="iconUrl" alt="" width="24" height="24"
                             style="image-rendering:pixelated;" x-cloak>
                        <span x-show="!iconUrl" style="color:#252560;font-size:0.7rem;" aria-hidden="true">◇</span>
                    </div>
                    <select id="item_id" name="item_id" autocomplete="off"
                            style="{{ $sel }}flex:1;"
                            x-on:change="onChange($event)">
                        <option value="">── selecione um item craftável ──</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}"
                                    data-icon="{{ $item->iconUrl }}"
                                    {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('item_id')
                    <p class="ff-error mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Servidor --}}
            <div>
                <label for="server_id" class="ff-label">
                    Servidor <span style="color:#ff5533;" aria-label="obrigatório">*</span>
                </label>
                <select id="server_id" name="server_id" style="{{ $sel }}">
                    <option value="">── selecione o servidor ──</option>
                    @foreach($servers->groupBy('region') as $region => $regionServers)
                        <optgroup label="{{ $region }}">
                            @foreach($regionServers as $server)
                                <option value="{{ $server->id }}" {{ old('server_id') == $server->id ? 'selected' : '' }}>
                                    {{ $server->datacenter }} — {{ $server->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('server_id')
                    <p class="ff-error mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lucro + Margem --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="min_profit" class="ff-label">
                        Lucro Mínimo <span style="color:var(--dim);">(gil)</span>
                        <span style="color:#ff5533;" aria-label="obrigatório">*</span>
                    </label>
                    <input type="number" id="min_profit" name="min_profit"
                           value="{{ old('min_profit', 5000) }}" min="0" step="1000"
                           autocomplete="off"
                           style="{{ $sel }}">
                    @error('min_profit')
                        <p class="ff-error mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="min_margin" class="ff-label">
                        Margem Mínima <span style="color:var(--dim);">(%)</span>
                        <span style="color:#ff5533;" aria-label="obrigatório">*</span>
                    </label>
                    <input type="number" id="min_margin" name="min_margin"
                           value="{{ old('min_margin', 20) }}" min="0" max="100" step="5"
                           autocomplete="off"
                           style="{{ $sel }}">
                    @error('min_margin')
                        <p class="ff-error mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="ff-divider"></div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-1">
                <button type="submit" class="ff-btn-primary">
                    Criar Alerta
                </button>
                <a href="{{ route('alerts.index') }}" class="ff-btn-ghost">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
