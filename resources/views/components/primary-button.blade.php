<button {{ $attributes->merge(['type' => 'submit']) }}
    style="font-family:'Cinzel',Georgia,serif;font-size:0.7rem;letter-spacing:0.14em;text-transform:uppercase;background:linear-gradient(160deg,#141440 0%,#1e1e60 100%);border:1px solid #f0c030;color:#f0c030;padding:0.55rem 1.2rem;cursor:pointer;transition:all 120ms ease;display:inline-flex;align-items:center;gap:0.4rem;white-space:nowrap;"
    onmouseover="this.style.background='linear-gradient(160deg,#1e1e60 0%,#28288a 100%)';this.style.color='#ffe880';this.style.borderColor='#ffe880';this.style.boxShadow='0 0 20px rgba(240,192,48,0.2)'"
    onmouseout="this.style.background='linear-gradient(160deg,#141440 0%,#1e1e60 100%)';this.style.color='#f0c030';this.style.borderColor='#f0c030';this.style.boxShadow='none'">
    <span style="font-size:0.5rem;">►</span>
    {{ $slot }}
</button>
