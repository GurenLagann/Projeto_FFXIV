<x-guest-layout>
    <h2 style="font-family:'Cinzel',serif;font-size:0.9rem;letter-spacing:0.1em;color:#8899cc;margin-bottom:1rem;text-align:center;">
        Recuperar Senha
    </h2>

    <p style="font-family:'Share Tech Mono',monospace;font-size:0.65rem;color:var(--hi-label,#7878c8);letter-spacing:0.04em;line-height:1.6;margin-bottom:1.5rem;text-align:center;">
        Informe seu e-mail e enviaremos um link para redefinir a senha.
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.75rem;">
            <x-primary-button style="width:100%;justify-content:center;">
                {{ __('Enviar Link') }}
            </x-primary-button>

            {{-- #5599ff = 7.1:1 on bg — WCAG AA ✓ --}}
            <a href="{{ route('login') }}"
               style="font-family:'Cinzel',serif;font-size:0.6rem;letter-spacing:0.08em;color:#5599ff;text-align:center;transition:color 120ms;text-decoration:none;"
               onmouseover="this.style.color='#88bbff'" onmouseout="this.style.color='#5599ff'">
                ← Voltar ao login
            </a>
        </div>
    </form>
</x-guest-layout>
