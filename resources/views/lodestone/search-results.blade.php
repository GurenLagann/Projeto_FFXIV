@extends('layouts.app')

@section('title', 'Selecionar Personagem — Lodestone')

@section('content')

<div class="mb-7 flex items-center justify-between">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; LODESTONE</p>
        <h1 class="ff-title text-2xl">Selecionar Personagem</h1>
    </div>
    <a href="{{ route('lodestone.show') }}" class="ff-btn-ghost" style="font-size:0.6rem;">
        ◄ Voltar
    </a>
</div>

@if(empty($results))
    <div class="ff-box max-w-xl p-12 text-center">
        <div style="font-size:2.5rem;color:#252560;margin-bottom:1rem;" aria-hidden="true">◇</div>
        <p class="ff-label" style="color:#3a4060;font-size:0.65rem;">Nenhum personagem encontrado</p>
        <p style="font-size:0.75rem;color:#2a3050;margin-top:0.4rem;margin-bottom:1.2rem;">
            Verifique o nome e o servidor e tente novamente.
        </p>
        <a href="{{ route('lodestone.show') }}" class="ff-btn-ghost" style="display:inline-flex;">
            Tentar novamente ►
        </a>
    </div>
@else
    <p class="ff-label mb-4" style="font-size:0.6rem;color:#4a5470;">
        {{ count($results) }} personagem(ns) encontrado(s) — selecione o seu
    </p>

    <div class="max-w-xl space-y-2">
        @foreach($results as $character)
        <form method="POST" action="{{ route('lodestone.confirm') }}">
            @csrf
            <input type="hidden" name="lodestone_id"     value="{{ $character['ID'] }}">
            <input type="hidden" name="character_name"   value="{{ $character['Name'] }}">
            <input type="hidden" name="character_server" value="{{ $character['Server'] }}">
            <input type="hidden" name="character_avatar" value="{{ $character['Avatar'] ?? '' }}">

            <button type="submit" class="w-full ff-box ff-card-hover text-left"
                    style="background:rgba(9,9,24,0.97);display:flex;align-items:center;gap:1rem;padding:1rem 1.2rem;cursor:pointer;border:1px solid #252560;transition:border-color 120ms,box-shadow 120ms;">
                @if(!empty($character['Avatar']))
                    <img src="{{ $character['Avatar'] }}" alt="Avatar"
                         style="width:48px;height:48px;object-fit:cover;flex-shrink:0;border:1px solid #252560;">
                @else
                    <div style="width:48px;height:48px;background:#0f0f28;flex-shrink:0;border:1px solid #252560;display:flex;align-items:center;justify-content:center;color:#252560;font-size:1.2rem;">◇</div>
                @endif
                <div style="flex:1;min-width:0;">
                    <p style="font-family:'Cinzel',serif;font-size:0.9rem;color:#ccd4f0;letter-spacing:0.03em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $character['Name'] }}
                    </p>
                    <p class="ff-num" style="font-size:0.72rem;color:#4a5470;margin-top:2px;">
                        {{ $character['Server'] }}
                    </p>
                </div>
                <span class="ff-label" style="font-size:0.58rem;color:#4040a0;flex-shrink:0;">
                    Selecionar ►
                </span>
            </button>
        </form>
        @endforeach
    </div>
@endif

@endsection
