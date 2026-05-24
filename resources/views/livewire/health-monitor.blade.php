<div wire:init="runChecks">

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="ff-title text-2xl mb-1">Status das APIs</h1>
            <p class="text-[var(--dim)] text-xs tracking-wide">
                Monitoramento das integrações externas e serviços internos
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($checkedAt)
                <span class="ff-label" style="color:var(--dim);">
                    Última verificação: {{ $checkedAt }}
                </span>
            @endif

            <button wire:click="runChecks"
                    wire:loading.attr="disabled"
                    wire:target="runChecks"
                    class="ff-btn-primary">
                <span wire:loading.remove wire:target="runChecks"
                      class="inline-flex items-center gap-1.5">
                    <span style="font-size:1rem;line-height:1;">↻</span> Verificar agora
                </span>
                <span wire:loading wire:target="runChecks"
                      class="inline-flex items-center gap-1.5">
                    <span class="inline-block animate-spin" style="font-size:1rem;line-height:1;">↻</span>
                    Verificando...
                </span>
            </button>
        </div>
    </div>

    {{-- ── Loading state (before wire:init fires) ───────────────────────── --}}
    @if(empty($apis))
        <div class="ff-box p-10 mb-4">
            <div class="flex flex-col items-center gap-4">
                <div class="atb-track w-48"><div class="atb-fill"></div></div>
                <p class="ff-label" style="color:var(--dim);">Verificando conectividade das APIs...</p>
            </div>
        </div>
    @else

    {{-- ── APIs Externas ──────────────────────────────────────────────────── --}}
    <div class="mb-2">
        <h2 class="ff-label mb-3" style="color:var(--hi);letter-spacing:0.2em;">▸ APIs Externas</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach($apis as $api)
                @php
                    $isUp       = $api['status'] === 'up';
                    $isDegraded = $api['status'] === 'degraded';
                    $isDown     = $api['status'] === 'down';

                    [$dotColor, $borderColor, $bgColor, $label] = match($api['status']) {
                        'up'       => ['#00dd77', 'rgba(0,221,119,0.35)',  'rgba(0,50,25,0.3)',      'ONLINE'],
                        'degraded' => ['#f0c030', 'rgba(240,192,48,0.4)', 'rgba(60,48,0,0.3)',      'DEGRADADO'],
                        default    => ['#ff5533', 'rgba(255,85,51,0.4)',  'rgba(60,10,0,0.3)',      'OFFLINE'],
                    };

                    $msColor = match(true) {
                        $api['ms'] < 400  => 'var(--mako)',
                        $api['ms'] < 1200 => 'var(--gold)',
                        $api['ms'] < 3000 => '#ff9922',
                        default           => 'var(--fire)',
                    };

                    // Latency bar: cap at 4000ms = 100%
                    $barPct   = min(100, round($api['ms'] / 40));
                    $barColor = $msColor;
                @endphp

                <div class="ff-box p-5 flex flex-col gap-3"
                     style="border-color: {{ $borderColor }}; background: {{ $bgColor }};">

                    {{-- Name + status badge --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="ff-heading text-sm font-semibold mb-0.5">{{ $api['name'] }}</div>
                            <div class="text-[0.6rem] tracking-wide" style="color:var(--dim);font-family:'Share Tech Mono',monospace;">
                                {{ $api['endpoint'] }}
                            </div>
                        </div>
                        <span class="ff-label shrink-0 px-2 py-0.5 border text-[0.55rem]"
                              style="border-color:{{ $borderColor }};color:{{ $dotColor }};background:{{ $bgColor }};">
                            <span style="font-size:0.5rem;vertical-align:middle;">●</span>
                            {{ $label }}
                        </span>
                    </div>

                    {{-- Latency --}}
                    <div>
                        <div class="flex items-baseline gap-1 mb-1.5">
                            <span class="ff-num text-3xl font-bold" style="color:{{ $msColor }};">
                                {{ number_format($api['ms']) }}
                            </span>
                            <span class="text-xs" style="color:var(--dim);">ms</span>
                            @if(count($apis) > 1)
                                <span class="text-[0.55rem] ml-1" style="color:var(--dim);">(paralelo)</span>
                            @endif
                        </div>
                        {{-- Latency bar --}}
                        <div class="w-full rounded-none" style="height:2px;background:rgba(255,255,255,0.06);">
                            <div style="width:{{ $barPct }}%;height:2px;background:{{ $barColor }};transition:width 0.6s ease;"></div>
                        </div>
                    </div>

                    {{-- Message + purpose --}}
                    <div class="space-y-1">
                        <p class="text-xs" style="color:{{ $dotColor }};">{{ $api['message'] }}</p>
                        <p class="text-[0.65rem]" style="color:var(--dim);">{{ $api['purpose'] }}</p>
                    </div>

                    {{-- Footer: cache TTL + fragile warning --}}
                    <div class="flex items-center justify-between pt-1 border-t" style="border-color:rgba(37,37,96,0.5);">
                        <div class="flex items-center gap-1.5">
                            <span class="ff-label" style="color:var(--dim);">Cache TTL</span>
                            <span class="ff-num text-[0.65rem]" style="color:var(--dim);">{{ $api['cacheTtl'] }}</span>
                        </div>
                        @if($api['fragile'])
                            <span class="ff-label text-[0.5rem] px-1.5 py-0.5 border"
                                  style="border-color:rgba(240,192,48,0.3);color:rgba(240,192,48,0.7);"
                                  title="Integração por scraping HTML — pode quebrar em atualizações do site">
                                ⚠ HTML SCRAPING
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Sistema ─────────────────────────────────────────────────────────── --}}
    <div>
        <h2 class="ff-label mb-3" style="color:var(--hi);letter-spacing:0.2em;">▸ Sistema</h2>
        <div class="ff-box overflow-hidden">
            <table class="ff-table w-full">
                <thead>
                    <tr>
                        <th class="text-left" style="width:180px;">Serviço</th>
                        <th class="text-left" style="width:130px;">Status</th>
                        <th class="text-left" style="width:120px;">Driver</th>
                        <th class="text-left">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($system as $svc)
                        @php
                            [$dotColor, $bgColor, $label] = match($svc['status']) {
                                'up'       => ['#00dd77', 'rgba(0,221,119,0.06)',  'ONLINE'],
                                'degraded' => ['#f0c030', 'rgba(240,192,48,0.06)', 'DEGRADADO'],
                                default    => ['#ff5533', 'rgba(255,85,51,0.06)',  'OFFLINE'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span style="font-size:1rem;">{{ $svc['icon'] }}</span>
                                    <span class="ff-heading text-sm">{{ $svc['name'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="ff-label px-2 py-0.5 text-[0.55rem] inline-flex items-center gap-1"
                                      style="color:{{ $dotColor }};background:{{ $bgColor }};">
                                    <span style="font-size:0.45rem;">●</span>
                                    {{ $label }}
                                </span>
                                <div class="text-xs mt-0.5" style="color:var(--dim);">{{ $svc['message'] }}</div>
                            </td>
                            <td>
                                <span class="ff-num text-[0.65rem] px-2 py-0.5"
                                      style="background:rgba(37,37,96,0.4);color:var(--crystal);">
                                    {{ $svc['badge'] }}
                                </span>
                            </td>
                            <td class="text-xs" style="color:var(--dim);">
                                {{ $svc['detail'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @endif {{-- /empty($apis) --}}

    {{-- ── Nota de rodapé ─────────────────────────────────────────────────── --}}
    <div class="mt-6 flex items-center gap-2" style="opacity:0.5;">
        <span class="ff-label text-[0.55rem]" style="color:var(--dim);">
            ✦ &nbsp; Checks de API rodam em paralelo (Http::pool) · Sistema verificado em tempo real
        </span>
    </div>

</div>
