@extends('layouts.app')

@section('title', 'Personagem FFXIV — Lodestone')

@section('content')

<div class="mb-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; LODESTONE</p>
        <h1 class="ff-title text-2xl">Personagem FFXIV</h1>
        <p class="mt-1.5" style="font-size:0.78rem;color:#4a5470;">
            Vincule seu personagem para filtros automáticos de nível.
        </p>
    </div>
    <a href="{{ route('market.dashboard') }}" class="ff-btn-ghost self-start sm:self-auto" style="font-size:0.6rem;">
        ◄ Dashboard
    </a>
</div>

@if(auth()->user()->isCharacterVerified())
    {{-- ══ Personagem verificado ══ --}}
    <div class="ff-box max-w-2xl p-6">

        {{-- Avatar + nome --}}
        <div class="flex items-center gap-5 mb-6">
            @if(auth()->user()->character_avatar)
                <div style="position:relative;flex-shrink:0;">
                    <img src="{{ auth()->user()->character_avatar }}" alt="Avatar de {{ auth()->user()->character_name }}"
                         style="width:72px;height:72px;object-fit:cover;border:1px solid #4040a0;">
                    {{-- Verified badge --}}
                    <span style="position:absolute;bottom:-4px;right:-4px;width:18px;height:18px;background:#00dd77;border:2px solid #06060f;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:8px;color:#003320;">✓</span>
                </div>
            @endif
            <div>
                <p style="font-family:'Cinzel',serif;font-size:1.1rem;color:#ccd4f0;letter-spacing:0.04em;">
                    {{ auth()->user()->character_name }}
                </p>
                <p class="ff-num" style="font-size:0.8rem;color:#4a5470;margin-top:2px;">
                    {{ auth()->user()->character_server }}
                </p>
                <p class="ff-label" style="font-size:0.55rem;color:#00dd77;margin-top:4px;">
                    ✦ &nbsp;Verificado em {{ auth()->user()->character_verified_at->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="ff-divider mb-5"></div>

        {{-- Job levels (FF5 crystal stats style) --}}
        @if(auth()->user()->job_levels)
        @php
            $crafterJobs = [
                8  => ['name' => 'Carpenter',     'abbr' => 'CRP', 'color' => '#88aa66'],
                9  => ['name' => 'Blacksmith',    'abbr' => 'BSM', 'color' => '#cc8844'],
                10 => ['name' => 'Armorer',       'abbr' => 'ARM', 'color' => '#8899bb'],
                11 => ['name' => 'Goldsmith',     'abbr' => 'GSM', 'color' => '#f0c030'],
                12 => ['name' => 'Leatherworker', 'abbr' => 'LTW', 'color' => '#aa7744'],
                13 => ['name' => 'Weaver',        'abbr' => 'WVR', 'color' => '#9966bb'],
                14 => ['name' => 'Alchemist',     'abbr' => 'ALC', 'color' => '#66bbaa'],
                15 => ['name' => 'Culinarian',    'abbr' => 'CUL', 'color' => '#cc6655'],
            ];
            $levels = auth()->user()->job_levels ?? [];
        @endphp

        <div class="mb-2">
            <p class="ff-label mb-3" style="font-size:0.58rem;color:#3a4a70;">Níveis de Crafting</p>
        </div>
        <div class="grid grid-cols-4 gap-2">
            @foreach($crafterJobs as $id => $job)
                @php $level = $levels[$id] ?? 0; @endphp
                <div class="ff-box p-3 text-center {{ $level > 0 ? '' : 'opacity-40' }}"
                     style="{{ $level > 0 ? 'border-color:'.$job['color'].'40;' : '' }}">
                    <p class="ff-label mb-1" style="font-size:0.5rem;color:{{ $level > 0 ? $job['color'] : '#3a4060' }};">
                        {{ $job['abbr'] }}
                    </p>
                    <p class="ff-num font-bold"
                       style="font-size:1.35rem;color:{{ $level >= 90 ? $job['color'] : ($level > 0 ? '#ccd4f0' : '#2a3050') }};">
                        {{ $level > 0 ? $level : '—' }}
                    </p>
                    @if($level > 0)
                        {{-- Mini level bar --}}
                        <div style="margin-top:6px;height:2px;background:#1a1a3a;overflow:hidden;">
                            <div style="height:100%;width:{{ min(100, ($level/100)*100) }}%;background:{{ $job['color'] }};opacity:0.7;"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        <div class="ff-divider my-5"></div>

        <form method="POST" action="{{ route('lodestone.unlink') }}"
              x-data x-on:submit.prevent="if(confirm('Desvincular personagem?')) $el.submit()">
            @csrf @method('DELETE')
            <button type="submit" class="ff-label"
                    style="font-size:0.6rem;color:#5a2020;background:none;border:none;cursor:pointer;transition:color 120ms;"
                    onmouseover="this.style.color='#ff5533'" onmouseout="this.style.color='#5a2020'">
                ✖ &nbsp;Desvincular personagem
            </button>
        </form>
    </div>

@elseif(auth()->user()->lodestone_id && auth()->user()->verification_code)
    {{-- ══ Verificação pendente ══ --}}
    <div class="ff-box max-w-2xl p-6" style="border-color:#3a3010;">

        <div class="flex items-center gap-3 mb-4">
            <span style="color:#f0c030;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.65rem;color:#c0a040;margin:0;">Verificação Pendente</h2>
        </div>

        <p style="font-size:0.82rem;color:#8a7040;margin-bottom:0.3rem;">
            Personagem selecionado:
            <span style="font-family:'Cinzel',serif;color:#f0c030;">{{ auth()->user()->character_name }}</span>
        </p>
        <p class="ff-num" style="font-size:0.75rem;color:#5a5030;margin-bottom:1.5rem;">
            {{ auth()->user()->character_server }}
        </p>

        {{-- Verification code box --}}
        <div class="ff-box p-5 mb-5" style="border-color:#5a4010;text-align:center;">
            <p class="ff-label mb-3" style="font-size:0.58rem;color:#5a4820;">
                Código de verificação — cole na bio do seu personagem na Lodestone
            </p>
            <p class="ff-num" style="font-size:1.5rem;letter-spacing:0.3em;color:#f0c030;font-weight:700;">
                {{ auth()->user()->verification_code }}
            </p>
            <p style="font-size:0.65rem;color:#4a3820;margin-top:0.6rem;">
                Acesse:
                <a href="https://na.finalfantasyxiv.com/lodestone/my/setting/profile/" target="_blank"
                   style="color:#5599ff;text-decoration:none;"
                   onmouseover="this.style.textDecoration='underline'"
                   onmouseout="this.style.textDecoration='none'">
                    Lodestone → Configurações → Introdução
                </a>
            </p>
        </div>

        <form method="POST" action="{{ route('lodestone.verify') }}">
            @csrf
            @if($errors->any())
                <p class="ff-error mb-3">{{ $errors->first() }}</p>
            @endif
            <div class="flex gap-3">
                <button type="submit" class="ff-btn-primary">
                    Verificar agora
                </button>
            </div>
        </form>
    </div>

@else
    {{-- ══ Formulário de busca ══ --}}
    <div class="max-w-2xl space-y-5">
        <p style="font-size:0.8rem;color:#4a5470;margin-bottom:0.5rem;line-height:1.7;">
            Vincule seu personagem FFXIV para que o analisador use seus níveis de crafting como filtros automáticos.
            A verificação é feita via Lodestone — sem senha, sem acesso à conta Square Enix.
        </p>

        {{-- Search error / API notice --}}
        @if($errors->has('search'))
            <div class="ff-alert-error flex items-start gap-3 px-4 py-3" style="font-size:0.8rem;">
                <span style="flex-shrink:0;margin-top:1px;">⚠</span>
                <span>{{ $errors->first('search') }}</span>
            </div>
        @endif

        {{-- Search by name --}}
        <div class="ff-box p-6">
            <div class="flex items-center gap-3 mb-5">
                <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
                <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Buscar por Nome</h2>
            </div>
            <div class="ff-divider mb-5"></div>

            <form method="POST" action="{{ route('lodestone.search') }}" class="space-y-4">
                @csrf
                @error('character_name')
                    <p class="ff-error">{{ $message }}</p>
                @enderror
                @error('character_server')
                    <p class="ff-error">{{ $message }}</p>
                @enderror
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="ff-label">Nome do Personagem <span style="color:#ff5533;">*</span></label>
                        <input type="text" name="character_name" value="{{ old('character_name') }}"
                               placeholder="Ex: Warrior of Light"
                               style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;padding:0.55rem 0.8rem;font-size:0.875rem;border-radius:0;width:100%;">
                    </div>
                    <div>
                        <label class="ff-label">Servidor <span style="color:#ff5533;">*</span></label>
                        <select name="character_server"
                                style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;padding:0.55rem 0.8rem;font-size:0.875rem;border-radius:0;width:100%;">
                            <option value="">── selecione ──</option>
                            @foreach($servers->groupBy('region') as $region => $regionServers)
                                <optgroup label="{{ $region }}">
                                    @foreach($regionServers as $server)
                                        <option value="{{ $server->name }}"
                                                {{ old('character_server') == $server->name ? 'selected' : '' }}>
                                            {{ $server->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit" class="ff-btn-primary">
                        Buscar personagem
                    </button>
                </div>
            </form>
        </div>

        {{-- Manual ID entry (fallback) --}}
        <div class="ff-box p-6" style="border-color:#2a2a50;"
             x-data="{ open: {{ ($errors->has('lodestone_id') || $errors->has('search')) ? 'true' : 'false' }} }">

            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between gap-3"
                    style="background:none;border:none;cursor:pointer;padding:0;">
                <div class="flex items-center gap-3">
                    <span style="color:#3a3a70;font-size:0.6rem;" aria-hidden="true">◇</span>
                    <span class="ff-label" style="font-size:0.6rem;color:#4a5070;">
                        Entrar ID da Lodestone manualmente
                    </span>
                </div>
                <span class="ff-label" x-text="open ? '▲' : '▼'" style="font-size:0.5rem;color:#3a3a60;"></span>
            </button>

            <div x-show="open" style="display:none;">
                <div class="ff-divider my-4"></div>
                <p style="font-size:0.75rem;color:#3a4060;margin-bottom:1rem;line-height:1.65;">
                    Acesse seu perfil na
                    <a href="https://na.finalfantasyxiv.com/lodestone/my/" target="_blank"
                       style="color:#5599ff;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">Lodestone</a>
                    e copie o número da URL:<br>
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.72rem;color:#4a5470;">
                        finalfantasyxiv.com/lodestone/character/<span style="color:#f0c030;">12345678</span>/
                    </span>
                </p>
                <form method="POST" action="{{ route('lodestone.byid') }}" class="flex items-end gap-3">
                    @csrf
                    <div style="flex:1;">
                        <label class="ff-label">ID do Personagem <span style="color:#ff5533;">*</span></label>
                        @error('lodestone_id')
                            <p class="ff-error mb-1">{{ $message }}</p>
                        @enderror
                        <input type="number" name="lodestone_id" value="{{ old('lodestone_id') }}"
                               placeholder="Ex: 12345678" min="1" max="99999999"
                               style="background:rgba(4,4,14,0.95);border:1px solid #252560;color:#ccd4f0;padding:0.55rem 0.8rem;font-size:0.875rem;border-radius:0;width:100%;">
                    </div>
                    <button type="submit" class="ff-btn-ghost" style="white-space:nowrap;flex-shrink:0;">
                        Usar este ID ►
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection
