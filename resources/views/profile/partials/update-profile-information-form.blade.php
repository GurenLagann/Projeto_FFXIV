<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)"
                          required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)"
                          required autocomplete="username" />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p style="font-size:0.75rem;color:#7a6020;">
                        E-mail não verificado.
                        <button form="send-verification"
                                style="color:#f0c030;background:none;border:none;cursor:pointer;font-size:0.75rem;text-decoration:underline;">
                            Reenviar verificação.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p style="margin-top:0.25rem;font-size:0.72rem;color:#00dd77;">
                            Link de verificação enviado.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Salvar') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="ff-label" style="font-size:0.65rem;color:#00dd77;">
                    ✦ Salvo com sucesso
                </p>
            @endif
        </div>
    </form>
</section>
