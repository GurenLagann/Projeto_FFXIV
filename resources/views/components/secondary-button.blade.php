<button {{ $attributes->merge(['type' => 'button']) }}
    style="font-family:'Cinzel',Georgia,serif;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;background:transparent;border:1px solid #252560;color:#4a5470;padding:0.55rem 1.1rem;cursor:pointer;transition:all 120ms ease;display:inline-flex;align-items:center;gap:0.4rem;white-space:nowrap;"
    onmouseover="this.style.borderColor='#4040a0';this.style.color='#5599ff';"
    onmouseout="this.style.borderColor='#252560';this.style.color='#4a5470';">
    {{ $slot }}
</button>
