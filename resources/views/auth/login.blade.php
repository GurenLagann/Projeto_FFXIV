<x-guest-layout>
    <x-auth-session-status :status="session('status')" />

    <h2 style="font-family:'Cinzel',serif;font-size:0.9rem;letter-spacing:0.1em;color:#8899cc;margin-bottom:1.5rem;text-align:center;">
        Acessar Conta
    </h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1rem;">
            <x-input-label for="password" :value="__('Senha')" />
            <x-text-input id="password" type="password" name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" style="font-size:0.7rem;color:#ff5533;" />
        </div>

        <div style="margin-top:1rem;display:flex;align-items:center;gap:0.5rem;">
            {{-- Custom checkbox: CSS :checked via JS; checkmark via ::after pseudo --}}
            <input id="remember_me" type="checkbox" name="remember"
                   style="appearance:none;width:14px;height:14px;background:rgba(4,4,14,0.98);border:1px solid #252560;border-radius:0;cursor:pointer;flex-shrink:0;position:relative;transition:border-color 120ms,background 120ms;"
                   onclick="this.style.borderColor=this.checked?'#5599ff':'#252560';this.style.background=this.checked?'rgba(85,153,255,0.15)':'rgba(4,4,14,0.98)';this.style.outline=this.checked?'none':''">
            <label for="remember_me"
                   style="font-family:'Cinzel',serif;font-size:0.6rem;letter-spacing:0.1em;color:#7878c8;cursor:pointer;">
                Lembrar-me
            </label>
        </div>

        <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.75rem;">
            <x-primary-button style="width:100%;justify-content:center;">
                {{ __('Entrar') }}
            </x-primary-button>

            @if (Route::has('password.request'))
                {{-- #5599ff = 7.1:1 on bg — WCAG AA ✓ --}}
                <a href="{{ route('password.request') }}"
                   style="font-family:'Cinzel',serif;font-size:0.6rem;letter-spacing:0.08em;color:#5599ff;text-align:center;transition:color 120ms;text-decoration:none;"
                   onmouseover="this.style.color='#88bbff'" onmouseout="this.style.color='#5599ff'">
                    Esqueceu a senha?
                </a>
            @endif
        </div>
    </form>

    <div style="margin-top:1.5rem;padding-top:1.2rem;border-top:1px solid #252560;text-align:center;">
        {{-- #5599ff = 7.1:1 on bg — WCAG AA ✓ --}}
        <a href="{{ route('register') }}"
           style="font-family:'Cinzel',serif;font-size:0.6rem;letter-spacing:0.08em;color:#5599ff;transition:color 120ms;text-decoration:none;"
           onmouseover="this.style.color='#88bbff'" onmouseout="this.style.color='#5599ff'">
            Não tem conta? Registrar
        </a>
    </div>
</x-guest-layout>
