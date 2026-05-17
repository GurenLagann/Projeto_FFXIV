<x-guest-layout>
    <h2 style="font-family:'Cinzel',serif;font-size:0.9rem;letter-spacing:0.1em;color:#8899cc;margin-bottom:1.5rem;text-align:center;">
        Criar Conta
    </h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')"
                          required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1rem;">
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1rem;">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1rem;">
            <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.75rem;">
            <x-primary-button style="width:100%;justify-content:center;">
                {{ __('Registrar') }}
            </x-primary-button>

            <a href="{{ route('login') }}"
               style="font-family:'Cinzel',serif;font-size:0.6rem;letter-spacing:0.08em;color:#2e3a60;text-align:center;transition:color 120ms;text-decoration:none;"
               onmouseover="this.style.color='#5599ff'" onmouseout="this.style.color='#2e3a60'">
                Já tem conta? Entrar
            </a>
        </div>
    </form>
</x-guest-layout>
