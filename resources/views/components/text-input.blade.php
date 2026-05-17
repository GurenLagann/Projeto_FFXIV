@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'style' => 'background:rgba(4,4,14,0.98);border:1px solid #252560;color:#ccd4f0;padding:0.55rem 0.85rem;font-size:0.875rem;border-radius:0;width:100%;transition:border-color 120ms,box-shadow 120ms;outline:none;'
    ]) }}
    onfocus="this.style.borderColor='#5599ff';this.style.boxShadow='0 0 0 1px rgba(85,153,255,0.12),inset 0 0 10px rgba(85,153,255,0.04)'"
    onblur="this.style.borderColor='#252560';this.style.boxShadow='none'"
>
