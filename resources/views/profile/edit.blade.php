@extends('layouts.app')

@section('title', 'Perfil — FFXIV Market')

@section('content')

<div class="mb-7">
    <p class="ff-label mb-1" style="font-size:0.55rem;letter-spacing:0.2em;color:#2e3a60;">✦ &nbsp; CONFIGURAÇÕES DO AVENTUREIRO</p>
    <h1 class="ff-title text-2xl">Perfil</h1>
</div>

<div class="max-w-2xl space-y-5">

    {{-- Informações do Perfil --}}
    <div class="ff-box p-6">
        <div class="flex items-center gap-3 mb-4">
            <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Informações da Conta</h2>
        </div>
        <div class="ff-divider mb-5"></div>
        @include('profile.partials.update-profile-information-form')
    </div>

    {{-- Senha --}}
    <div class="ff-box p-6">
        <div class="flex items-center gap-3 mb-4">
            <span style="color:#4040a0;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.62rem;color:#8899cc;margin:0;">Alterar Senha</h2>
        </div>
        <div class="ff-divider mb-5"></div>
        @include('profile.partials.update-password-form')
    </div>

    {{-- Excluir Conta --}}
    <div class="ff-box p-6" style="border-color:#3a1515;">
        <div class="flex items-center gap-3 mb-4">
            <span style="color:#ff5533;font-size:0.6rem;" aria-hidden="true">◆</span>
            <h2 class="ff-label" style="font-size:0.62rem;color:#804040;margin:0;">Zona de Perigo</h2>
        </div>
        <div style="height:1px;background:linear-gradient(90deg,transparent,#5a1515,#ff553340,#5a1515,transparent);opacity:0.5;margin-bottom:1.25rem;"></div>
        @include('profile.partials.delete-user-form')
    </div>

</div>

@endsection
