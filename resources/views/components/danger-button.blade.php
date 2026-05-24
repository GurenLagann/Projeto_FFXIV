<button {{ $attributes->merge(['type' => 'submit']) }}
    style="font-family:'Cinzel',Georgia,serif;font-size:0.7rem;letter-spacing:0.14em;text-transform:uppercase;background:linear-gradient(160deg,#2a0808 0%,#4a1010 100%);border:1px solid #ff5533;color:#ff5533;padding:0.55rem 1.2rem;cursor:pointer;transition:background 120ms ease,color 120ms ease,box-shadow 120ms ease;display:inline-flex;align-items:center;gap:0.4rem;white-space:nowrap;"
    onmouseover="this.style.background='linear-gradient(160deg,#4a1010 0%,#6a1818 100%)';this.style.boxShadow='0 0 20px rgba(255,85,51,0.2)';this.style.color='#ff8866';"
    onmouseout="this.style.background='linear-gradient(160deg,#2a0808 0%,#4a1010 100%)';this.style.boxShadow='none';this.style.color='#ff5533';">
    <span style="font-size:0.5rem;">✖</span>
    {{ $slot }}
</button>
