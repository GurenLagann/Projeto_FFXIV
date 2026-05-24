<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FFXIV Market Analyzer')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Fonts: Cinzel (FF5 medieval) + Share Tech Mono (FF7 terminal) + Figtree (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cinzel+Decorative:wght@700&family=Figtree:wght@400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    {{-- Vite-compiled Tailwind + Alpine (Alpine v3 is bundled by Livewire v4 — no separate import) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --bg:       #06060f;
            --surface:  #0b0b1e;
            --card:     #0f0f28;
            --border:   #252560;
            --hi:       #4040a0;   /* structural: borders, brackets, decorative markers — no contrast req */
            --hi-label: #7878c8;   /* text use of hi — 5.1:1 on --bg, 4.75:1 on --card — WCAG AA */
            --crystal:  #5599ff;
            --mako:     #00dd77;
            --gold:     #f0c030;
            --materia:  #a855f7;
            --fire:     #ff5533;
            --text:     #ccd4f0;
            --dim:      #7280a0;   /* raised from #4a5470 — 5.1:1 on --bg, 4.74:1 on --card — WCAG AA */
        }

        html { scroll-behavior: smooth; }

        body {
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 90% 40% at 50% -5%, rgba(85,153,255,0.07) 0%, transparent 55%),
                radial-gradient(ellipse 50% 30% at 85% 95%, rgba(0,221,119,0.04) 0%, transparent 50%);
            color: var(--text);
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); }
        ::-webkit-scrollbar-thumb:hover { background: var(--hi); }

        /* ── Focus ── */
        :focus-visible { outline: 1px solid var(--crystal); outline-offset: 2px; }

        /* ══════════════════════════════════════
           FF WINDOW BOX
           Classic Final Fantasy menu panel
        ══════════════════════════════════════ */
        .ff-box {
            background: rgba(9, 9, 24, 0.97);
            border: 1px solid var(--border);
            box-shadow:
                inset 0 0 0 1px rgba(37,37,96,0.5),
                0 8px 40px rgba(0,0,0,0.7);
            position: relative;
        }

        /* Corner bracket decorations (FF7 menu style) */
        .ff-box::before,
        .ff-box::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-color: var(--hi);
            border-style: solid;
            pointer-events: none;
            z-index: 1;
        }
        .ff-box::before { top:  4px; left:  4px; border-width: 1px 0 0 1px; }
        .ff-box::after  { bottom: 4px; right: 4px; border-width: 0 1px 1px 0; }

        /* ── Typography ── */
        .ff-title {
            font-family: 'Cinzel', 'Georgia', serif;
            background: linear-gradient(135deg, #c08820 0%, #f0c030 40%, #ffe880 65%, #c08820 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.06em;
        }

        .ff-heading {
            font-family: 'Cinzel', Georgia, serif;
            color: var(--text);
            letter-spacing: 0.05em;
        }

        .ff-label {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.6rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--hi-label); /* #7878c8 — 5.1:1 WCAG AA on all dark surfaces */
        }

        .ff-num {
            font-family: 'Share Tech Mono', monospace;
        }

        /* ── Divider ── */
        .ff-divider {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--hi) 20%, var(--crystal) 50%, var(--hi) 80%, transparent 100%);
            opacity: 0.4;
        }

        /* ── Glow helpers ── */
        .crystal-glow { box-shadow: 0 0 18px rgba(85,153,255,0.35), 0 0 1px rgba(85,153,255,0.9); }
        .mako-glow    { box-shadow: 0 0 14px rgba(0,221,119,0.4),   0 0 1px rgba(0,221,119,0.8);  }
        .gold-glow    { box-shadow: 0 0 18px rgba(240,192,48,0.35), 0 0 1px rgba(240,192,48,0.7); }

        /* ── Inputs ── */
        .ff-input {
            background: rgba(4, 4, 14, 0.95);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.55rem 0.8rem;
            font-size: 0.875rem;
            border-radius: 0;
            width: 100%;
            transition: border-color 120ms, box-shadow 120ms;
        }
        .ff-input:focus {
            outline: none;
            border-color: var(--crystal);
            box-shadow: 0 0 0 1px rgba(85,153,255,0.12), inset 0 0 10px rgba(85,153,255,0.04);
        }
        .ff-input::placeholder { color: #3a4568; }

        /* ── Primary Button (FF gold) ── */
        .ff-btn-primary {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.7rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background: linear-gradient(160deg, #141440 0%, #1e1e60 100%);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 0.55rem 1.5rem;
            cursor: pointer;
            transition: background 120ms, box-shadow 120ms, color 120ms, border-color 120ms;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .ff-btn-primary:hover:not(:disabled) {
            background: linear-gradient(160deg, #1e1e60 0%, #28288a 100%);
            box-shadow: 0 0 22px rgba(240,192,48,0.25);
            color: #ffe880;
            border-color: #ffe880;
        }
        .ff-btn-primary:disabled { opacity: 0.35; cursor: not-allowed; }

        /* Secondary ghost button */
        .ff-btn-ghost {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.65rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--dim);
            padding: 0.45rem 1rem;
            cursor: pointer;
            transition: border-color 120ms, color 120ms;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .ff-btn-ghost:hover:not(:disabled) {
            border-color: var(--hi);
            color: var(--crystal);
        }
        .ff-btn-ghost:disabled { opacity: 0.25; cursor: not-allowed; }

        /* ── Table ── */
        .ff-table thead th {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.58rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--hi);
            border-bottom: 1px solid var(--border);
            padding: 0.55rem 1rem;
            font-weight: 400;
        }
        .ff-table tbody td {
            padding: 0.65rem 1rem;
            border-bottom: 1px solid rgba(37,37,96,0.35);
            font-size: 0.875rem;
            color: var(--text);
        }
        .ff-table tbody tr { transition: background 100ms; }
        .ff-table tbody tr:hover { background: rgba(64,64,160,0.09); }
        .ff-table tbody tr:last-child td { border-bottom: none; }

        /* ── Badges ── */
        .ff-badge-profit {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.72rem;
            color: var(--mako);
            border: 1px solid rgba(0,221,119,0.3);
            background: rgba(0,50,25,0.4);
            padding: 0.1rem 0.45rem;
        }
        .ff-badge-loss {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.72rem;
            color: var(--fire);
            border: 1px solid rgba(255,85,51,0.3);
            background: rgba(50,15,5,0.4);
            padding: 0.1rem 0.45rem;
        }
        .ff-badge-warn {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.72rem;
            color: var(--gold);
            border: 1px solid rgba(240,192,48,0.3);
            background: rgba(40,30,0,0.4);
            padding: 0.1rem 0.45rem;
        }
        .ff-badge-neutral {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.72rem;
            color: var(--dim);
            border: 1px solid var(--border);
            background: rgba(10,10,30,0.5);
            padding: 0.1rem 0.45rem;
        }

        /* ── ATB loading bar (FF Active Time Battle) ── */
        @keyframes atb-sweep {
            0%   { width: 0%; opacity: 1; }
            85%  { width: 95%; opacity: 1; }
            100% { width: 100%; opacity: 0.5; }
        }
        @keyframes atb-blink {
            0%, 100% { opacity: 0.5; }
            50%       { opacity: 1;   }
        }
        .atb-track {
            width: 100%;
            height: 3px;
            background: rgba(37,37,96,0.6);
            overflow: hidden;
        }
        .atb-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--crystal) 0%, var(--mako) 60%, var(--gold) 100%);
            box-shadow: 0 0 8px rgba(85,153,255,0.7);
            animation: atb-sweep 1.8s ease-in-out infinite;
        }

        /* ── Card hover ── */
        .ff-card-hover { transition: border-color 150ms, box-shadow 150ms; }
        .ff-card-hover:hover {
            border-color: var(--hi) !important;
            box-shadow: inset 0 0 0 1px rgba(64,64,160,0.2), 0 0 20px rgba(64,64,160,0.12) !important;
        }

        /* ── Flash alerts ── */
        .ff-alert-success {
            background: rgba(0,30,15,0.8);
            border: 1px solid rgba(0,221,119,0.35);
            color: var(--mako);
        }
        .ff-alert-error {
            background: rgba(30,8,4,0.8);
            border: 1px solid rgba(255,85,51,0.35);
            color: var(--fire);
        }

        /* ── Nav ── */
        .ff-nav {
            background: rgba(5, 5, 13, 0.97);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 20px rgba(0,0,0,0.6);
            backdrop-filter: blur(14px);
        }

        .nav-lnk {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.62rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--dim);
            transition: color 120ms, text-shadow 120ms;
            position: relative;
            padding-bottom: 1px;
        }
        .nav-lnk:hover { color: #9aa8c8; }
        .nav-lnk.active {
            color: var(--crystal);
            text-shadow: 0 0 10px rgba(85,153,255,0.5);
        }
        .nav-lnk.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--crystal);
            box-shadow: 0 0 6px rgba(85,153,255,0.8);
        }

        /* ── Skeleton ── */
        @keyframes ff-shimmer {
            0%, 100% { opacity: 0.3; }
            50%       { opacity: 0.6; }
        }
        .ff-skeleton {
            background: rgba(37,37,96,0.3);
            animation: ff-shimmer 1.6s ease-in-out infinite;
        }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, .ff-card-hover { transition: none !important; animation: none !important; }
        }

        /* ── Alpine init guard ── */
        [x-cloak] { display: none !important; }

        /* ── Mobile nav drawer link ── */
        .ff-mobile-link {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.65rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--dim);
            display: block;
            padding: 0.75rem 0;
            min-height: 44px;
            text-decoration: none;
            transition: color 120ms;
            border: none;
            background: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            line-height: 1.6;
        }
        .ff-mobile-link:hover  { color: #9aa8c8; }
        .ff-mobile-link.active { color: var(--crystal); text-shadow: 0 0 8px rgba(85,153,255,0.4); }

        /* ── Touch target floor (44px) for dense interactive elements ── */
        .ff-touch { min-height: 44px; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">

{{-- Navigation --}}
<nav class="ff-nav sticky top-0 z-50" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-12">

            {{-- Brand + desktop primary links --}}
            <div class="flex items-center gap-5">
                <a href="{{ route('market.dashboard') }}"
                   class="flex items-center gap-2.5 group"
                   aria-label="FFXIV Market Analyzer — Início">
                    <svg width="17" height="20" viewBox="0 0 32 36" fill="none" aria-hidden="true">
                        <polygon points="16,1 6,12 16,15" fill="#ffe880" opacity="0.95"/>
                        <polygon points="16,1 26,12 16,15" fill="#c08820" opacity="0.85"/>
                        <polygon points="6,12 16,15 16,31"  fill="#8a5c10"/>
                        <polygon points="26,12 16,15 16,31" fill="#d4931c"/>
                        <polygon points="16,1 26,12 16,31 6,12" fill="none" stroke="#f0c030" stroke-width="0.8"/>
                    </svg>
                    <span class="ff-title text-sm tracking-widest hidden sm:block">FFXIV MARKET</span>
                </a>

                <span class="w-px h-3.5 bg-[#252560] hidden sm:block" aria-hidden="true"></span>

                <a href="{{ route('items.index') }}"
                   class="nav-lnk hidden sm:block {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    Itens
                </a>
                <a href="{{ route('craft.index') }}"
                   class="nav-lnk hidden sm:block {{ request()->routeIs('craft.*') ? 'active' : '' }}">
                    Craft
                </a>
                <a href="{{ route('market.history') }}"
                   class="nav-lnk hidden sm:block {{ request()->routeIs('market.history') ? 'active' : '' }}">
                    Histórico
                </a>
                <a href="{{ route('health') }}"
                   class="nav-lnk hidden sm:block {{ request()->routeIs('health') ? 'active' : '' }}"
                   title="Status das APIs externas e serviços internos">
                    Status
                </a>
            </div>

            {{-- Desktop auth + mobile hamburger --}}
            <div class="flex items-center gap-4">

                {{-- Desktop auth (hidden on mobile) --}}
                @auth
                    <div class="hidden sm:flex items-center gap-4">
                        <a href="{{ route('alerts.index') }}"
                           class="nav-lnk {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                            Alertas
                        </a>
                        <a href="{{ route('lodestone.show') }}"
                           class="flex items-center gap-1.5 nav-lnk {{ request()->routeIs('lodestone.*') ? 'active' : '' }}"
                           title="{{ auth()->user()->isCharacterVerified() ? auth()->user()->character_name : 'Vincular personagem' }}">
                            @if(auth()->user()->isCharacterVerified())
                                <span class="w-1.5 h-1.5 rounded-full bg-[var(--mako)]"
                                      style="box-shadow:0 0 5px rgba(0,221,119,0.7);" aria-hidden="true"></span>
                            @endif
                            Personagem
                        </a>
                        <span class="w-px h-3.5 bg-[#252560]" aria-hidden="true"></span>
                        <span class="ff-label" style="color:#2e3a5a;max-width:7rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:bottom;"
                              title="{{ auth()->user()->name }}">
                            {{ strtoupper(auth()->user()->name) }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="nav-lnk hover:!text-[var(--fire)] hover:!text-shadow-none"
                                    aria-label="Sair da conta">
                                Sair
                            </button>
                        </form>
                    </div>
                @else
                    <div class="hidden sm:flex items-center gap-3">
                        <a href="{{ route('login') }}" class="nav-lnk">Entrar</a>
                        <a href="{{ route('register') }}" class="ff-btn-primary"
                           style="padding:0.3rem 0.9rem;font-size:0.6rem;">
                            Registrar
                        </a>
                    </div>
                @endauth

                {{-- Mobile hamburger (hidden on sm+) --}}
                <button type="button"
                        class="sm:hidden nav-lnk"
                        style="font-size:1.15rem;min-width:44px;min-height:44px;
                               display:flex;align-items:center;justify-content:center;padding:0;"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-controls="ff-mobile-menu"
                        aria-label="Menu de navegação">
                    <span x-text="mobileOpen ? '✕' : '☰'" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="ff-mobile-menu"
         x-show="mobileOpen"
         x-cloak
         style="border-top:1px solid #1a1a38;background:rgba(5,5,13,0.99);
                box-shadow:0 8px 32px rgba(0,0,0,0.7);"
         class="sm:hidden"
         @keydown.escape.window="mobileOpen = false">
        <nav class="max-w-7xl mx-auto px-6 py-2" aria-label="Menu mobile">

            {{-- Primary pages --}}
            <a href="{{ route('market.dashboard') }}"
               class="ff-mobile-link {{ request()->routeIs('market.dashboard') ? 'active' : '' }}">
                Início
            </a>
            <a href="{{ route('items.index') }}"
               class="ff-mobile-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                Itens
            </a>
            <a href="{{ route('craft.index') }}"
               class="ff-mobile-link {{ request()->routeIs('craft.*') ? 'active' : '' }}">
                Craft
            </a>
            <a href="{{ route('market.history') }}"
               class="ff-mobile-link {{ request()->routeIs('market.history') ? 'active' : '' }}">
                Histórico
            </a>
            <a href="{{ route('health') }}"
               class="ff-mobile-link {{ request()->routeIs('health') ? 'active' : '' }}">
                Status
            </a>

            {{-- Auth section --}}
            @auth
                <div style="height:1px;background:#1a1a38;margin:0.25rem 0;" aria-hidden="true"></div>
                <a href="{{ route('alerts.index') }}"
                   class="ff-mobile-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                    Alertas
                </a>
                <a href="{{ route('lodestone.show') }}"
                   class="ff-mobile-link {{ request()->routeIs('lodestone.*') ? 'active' : '' }}">
                    @if(auth()->user()->isCharacterVerified())
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-[var(--mako)]"
                              style="box-shadow:0 0 5px rgba(0,221,119,0.7);margin-right:0.4rem;vertical-align:middle;"
                              aria-hidden="true"></span>
                    @endif
                    Personagem
                </a>
                <div style="height:1px;background:#1a1a38;margin:0.25rem 0;" aria-hidden="true"></div>
                <span class="ff-label" style="font-size:0.48rem;color:#2e3a5a;letter-spacing:0.22em;display:block;padding:0.4rem 0;
                                              max-width:16rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                      title="{{ auth()->user()->name }}">
                    {{ strtoupper(auth()->user()->name) }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="mb-2">
                    @csrf
                    <button type="submit" class="ff-mobile-link" style="color:var(--fire);">
                        Sair
                    </button>
                </form>
            @else
                <div style="height:1px;background:#1a1a38;margin:0.25rem 0;" aria-hidden="true"></div>
                <a href="{{ route('login') }}" class="ff-mobile-link">Entrar</a>
                <a href="{{ route('register') }}" class="ff-mobile-link" style="color:var(--crystal);">
                    Registrar
                </a>
                <div class="pb-1"></div>
            @endauth
        </nav>
    </div>
</nav>

{{-- Flash messages --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))
        <div role="alert" aria-live="polite"
             class="mt-4 ff-alert-success flex items-center gap-3 px-4 py-3 ff-label" style="font-size:0.7rem;">
            <span aria-hidden="true">✦</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div role="alert" aria-live="assertive"
             class="mt-4 ff-alert-error flex items-center gap-3 px-4 py-3 ff-label" style="font-size:0.7rem;">
            <span aria-hidden="true">✖</span>
            {{ session('error') }}
        </div>
    @endif
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

<footer class="border-t border-[#252560]/50 mt-16 py-6 text-center" aria-label="Rodapé">
    {{-- Purely decorative — aria-hidden removes from accessibility tree --}}
    <p class="ff-label text-[0.55rem] tracking-[0.25em]" style="color:#2a3060;" aria-hidden="true">
        ✦ &nbsp; FFXIV MARKET ANALYZER &nbsp; ✦
    </p>
    <p class="mt-1" style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;color:#1e244a;letter-spacing:0.15em;" aria-hidden="true">
        UNIVERSALIS · XIVAPI · EORZEA
    </p>
</footer>

@livewireScripts
@stack('scripts')
</body>
</html>
