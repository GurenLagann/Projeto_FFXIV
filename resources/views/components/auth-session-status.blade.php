@props(['status'])

@if ($status)
    <div {{ $attributes }}
         style="font-size:0.72rem;color:#00dd77;letter-spacing:0.05em;padding:0.5rem 0.75rem;background:rgba(0,30,15,0.6);border:1px solid rgba(0,221,119,0.25);margin-bottom:1rem;">
        {{ $status }}
    </div>
@endif
