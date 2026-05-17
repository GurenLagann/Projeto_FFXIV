<section>
    <p style="font-size:0.78rem;color:#6a4040;margin-bottom:1.25rem;line-height:1.6;">
        Após a exclusão, todos os seus dados e alertas serão apagados permanentemente.
        Esta ação não pode ser desfeita.
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        {{ __('Excluir Conta') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}"
              style="background:rgba(9,9,24,0.98);border:1px solid #3a1515;padding:2rem;position:relative;">

            {{-- Modal corner decorations --}}
            <div style="position:absolute;top:6px;left:6px;width:12px;height:12px;border-top:1px solid #7a2020;border-left:1px solid #7a2020;"></div>
            <div style="position:absolute;bottom:6px;right:6px;width:12px;height:12px;border-bottom:1px solid #7a2020;border-right:1px solid #7a2020;"></div>

            @csrf
            @method('delete')

            <div class="flex items-center gap-2 mb-4">
                <span style="color:#ff5533;font-size:0.7rem;" aria-hidden="true">◆</span>
                <h2 class="ff-label" style="font-size:0.65rem;color:#804040;margin:0;">Confirmar Exclusão</h2>
            </div>

            <p style="font-size:0.82rem;color:#7a4040;margin-bottom:1.2rem;line-height:1.6;">
                Esta ação é permanente. Digite sua senha para confirmar.
            </p>

            <div class="mb-4">
                <x-input-label for="delete_password" :value="__('Senha')" />
                <x-text-input id="delete_password" name="password" type="password"
                              placeholder="••••••••" style="border-color:#5a1515;" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>
                <x-danger-button>
                    {{ __('Excluir permanentemente') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
