@props(['value'])

<label {{ $attributes }}
    style="font-family:'Cinzel',Georgia,serif;font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:#4040a0;display:block;margin-bottom:0.4rem;">
    {{ $value ?? $slot }}
</label>
