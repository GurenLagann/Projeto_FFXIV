@extends('layouts.app')

@section('title', 'Verificar Personagem — Lodestone')

@section('content')

<div class="mb-7 flex items-center justify-between">
    <div>
        <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; LODESTONE · VERIFICAÇÃO</p>
        <h1 class="ff-title text-2xl">Verificar Personagem</h1>
    </div>
    <a href="{{ route('lodestone.show') }}" class="ff-btn-ghost" style="font-size:0.6rem;">◄ Voltar</a>
</div>

<div class="max-w-xl">
    <div class="ff-box p-6 space-y-5">

        {{-- Personagem selecionado --}}
        <div>
            <p class="ff-label mb-1" style="font-size:0.58rem;color:#3a4a70;">Personagem selecionado</p>
            <p style="font-family:'Cinzel',serif;font-size:1rem;color:#f0c030;letter-spacing:0.04em;">
                {{ $character }}
            </p>
        </div>

        <div class="ff-divider"></div>

        {{-- Steps --}}
        <div class="space-y-4">

            <div class="flex gap-4">
                <div style="flex-shrink:0;width:22px;height:22px;background:linear-gradient(135deg,#141440,#1e1e60);border:1px solid #f0c030;display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#f0c030;">1</span>
                </div>
                <p style="font-size:0.82rem;color:#6a7090;line-height:1.65;padding-top:2px;">
                    Acesse o
                    <a href="https://na.finalfantasyxiv.com/lodestone/my/setting/profile/" target="_blank"
                       style="color:#5599ff;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'">
                        perfil na Lodestone
                    </a>
                    (requer login Square Enix).
                </p>
            </div>

            <div class="flex gap-4">
                <div style="flex-shrink:0;width:22px;height:22px;background:linear-gradient(135deg,#141440,#1e1e60);border:1px solid #f0c030;display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#f0c030;">2</span>
                </div>
                <p style="font-size:0.82rem;color:#6a7090;line-height:1.65;padding-top:2px;">
                    No campo <span style="color:#ccd4f0;font-weight:600;">"Introdução" (Bio)</span>, adicione o código abaixo em qualquer lugar do texto:
                </p>
            </div>
        </div>

        {{-- Verification code --}}
        <div class="ff-box p-5" style="border-color:#4a3a10;text-align:center;">
            <p class="ff-label mb-3" style="font-size:0.55rem;color:#4a3a18;letter-spacing:0.18em;">
                CÓDIGO DE VERIFICAÇÃO
            </p>
            <p class="ff-num" style="font-size:1.7rem;letter-spacing:0.35em;color:#f0c030;font-weight:700;">
                {{ $code }}
            </p>
            <p style="font-size:0.65rem;color:#3a2e10;margin-top:0.6rem;">
                Pode remover o código após a verificação.
            </p>
        </div>

        <div class="space-y-4">
            <div class="flex gap-4">
                <div style="flex-shrink:0;width:22px;height:22px;background:linear-gradient(135deg,#141440,#1e1e60);border:1px solid #f0c030;display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#f0c030;">3</span>
                </div>
                <p style="font-size:0.82rem;color:#6a7090;line-height:1.65;padding-top:2px;">
                    Salve o perfil na Lodestone.
                </p>
            </div>
            <div class="flex gap-4">
                <div style="flex-shrink:0;width:22px;height:22px;background:linear-gradient(135deg,#141440,#1e1e60);border:1px solid #f0c030;display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:#f0c030;">4</span>
                </div>
                <p style="font-size:0.82rem;color:#6a7090;line-height:1.65;padding-top:2px;">
                    Clique em <span style="color:#ccd4f0;font-weight:600;">"Verificar agora"</span>. Pode levar alguns minutos para a Lodestone atualizar.
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="ff-alert-error flex items-start gap-3 px-4 py-3" style="font-size:0.8rem;">
                <span style="flex-shrink:0;margin-top:1px;">✖</span>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="ff-divider"></div>

        <form method="POST" action="{{ route('lodestone.verify') }}">
            @csrf
            <div class="flex items-center gap-3">
                <button type="submit" class="ff-btn-primary">
                    Verificar agora
                </button>
                <a href="{{ route('lodestone.show') }}"
                   class="ff-btn-ghost">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
