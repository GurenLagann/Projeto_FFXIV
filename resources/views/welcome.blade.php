<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FFXIV Market Analyzer</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cinzel+Decorative:wght@400;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #04040c;
            --border:  #252560;
            --hi:      #4040a0;
            --crystal: #5599ff;
            --mako:    #00dd77;
            --gold:    #f0c030;
            --materia: #a855f7;
            --fire:    #ff5533;
            --text:    #ccd4f0;
            --dim:     #4a5470;
        }

        html, body { height: 100%; }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* ── Starfield background ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(1px 1px at 15% 25%, rgba(85,153,255,0.6) 0%, transparent 100%),
                radial-gradient(1px 1px at 78% 12%, rgba(240,192,48,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 42% 68%, rgba(85,153,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 91% 55%, rgba(168,85,247,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 6%  82%, rgba(0,221,119,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 63% 90%, rgba(85,153,255,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 33% 8%,  rgba(240,192,48,0.4) 0%, transparent 100%),
                radial-gradient(1px 1px at 87% 78%, rgba(85,153,255,0.5) 0%, transparent 100%),
                radial-gradient(1px 1px at 22% 48%, rgba(168,85,247,0.3) 0%, transparent 100%),
                radial-gradient(1px 1px at 55% 35%, rgba(0,221,119,0.3) 0%, transparent 100%),
                radial-gradient(ellipse 80% 40% at 50% -5%,  rgba(85,153,255,0.07) 0%, transparent 55%),
                radial-gradient(ellipse 50% 30% at 80%  95%, rgba(0,221,119,0.04) 0%, transparent 50%);
            pointer-events: none;
        }

        /* ── FF Window ── */
        .ff-box {
            background: rgba(8, 8, 22, 0.97);
            border: 1px solid var(--border);
            box-shadow:
                inset 0 0 0 1px rgba(37,37,96,0.5),
                0 12px 60px rgba(0,0,0,0.9);
            position: relative;
        }
        .ff-box::before,
        .ff-box::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-color: var(--hi);
            border-style: solid;
            pointer-events: none;
        }
        .ff-box::before { top:  6px; left:  6px; border-width: 1px 0 0 1px; }
        .ff-box::after  { bottom: 6px; right: 6px; border-width: 0 1px 1px 0; }

        /* ── Typography ── */
        .ff-title {
            font-family: 'Cinzel Decorative', 'Cinzel', Georgia, serif;
            background: linear-gradient(135deg, #a07010 0%, #f0c030 35%, #ffe880 55%, #c08820 80%, #a07010 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.08em;
            line-height: 1.15;
        }

        .ff-subtitle {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.65rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #2e3a60;
        }

        .ff-divider {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--hi) 20%, var(--crystal) 50%, var(--hi) 80%, transparent 100%);
            opacity: 0.4;
        }

        /* ── Buttons ── */
        .ff-btn-enter {
            width: 100%;
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.75rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            background: linear-gradient(160deg, #141440 0%, #1e1e60 100%);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: all 120ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            text-decoration: none;
        }
        .ff-btn-enter:hover {
            background: linear-gradient(160deg, #1e1e60 0%, #28288a 100%);
            box-shadow: 0 0 26px rgba(240,192,48,0.22);
            color: #ffe880;
            border-color: #ffe880;
        }

        .ff-btn-secondary {
            width: 100%;
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.68rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--dim);
            padding: 0.65rem 1.5rem;
            cursor: pointer;
            transition: all 120ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .ff-btn-secondary:hover {
            border-color: var(--hi);
            color: var(--crystal);
        }

        /* ── Crystal icons ── */
        .crystal-el {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
        }
        .crystal-el span {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.55rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        /* ── Floating animation ── */
        @keyframes float-y {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        @keyframes glow-pulse {
            0%,100% { opacity: 0.35; }
            50%      { opacity: 0.75; }
        }

        .float-anim  { animation: float-y 5s ease-in-out infinite; }
        .float-anim2 { animation: float-y 7s ease-in-out infinite; animation-delay: 1s; }
        .float-anim3 { animation: float-y 6s ease-in-out infinite; animation-delay: 2s; }
        .float-anim4 { animation: float-y 8s ease-in-out infinite; animation-delay: 0.5s; }
        .glow-blink  { animation: glow-pulse 3s ease-in-out infinite; }

        /* ── Presss Start blink cursor ── */
        @keyframes blink { 0%,100%{ opacity:1; } 50%{ opacity:0; } }
        .cursor-blink { animation: blink 1s step-end infinite; }
    </style>
</head>
<body>

    {{-- ── Floating Crystal Decorations ── --}}
    <div style="position:fixed;inset:0;pointer-events:none;overflow:hidden;" aria-hidden="true">
        <div class="float-anim glow-blink" style="position:absolute;top:8%;left:5%;color:#5599ff;opacity:0.2;font-size:2.5rem;">◆</div>
        <div class="float-anim2 glow-blink" style="position:absolute;top:18%;right:8%;color:#f0c030;opacity:0.15;font-size:3rem;">◇</div>
        <div class="float-anim3" style="position:absolute;bottom:28%;left:7%;color:#00dd77;opacity:0.15;font-size:2rem;">◆</div>
        <div class="float-anim4 glow-blink" style="position:absolute;bottom:18%;right:6%;color:#a855f7;opacity:0.15;font-size:2.5rem;">◇</div>
        <div class="float-anim2" style="position:absolute;top:55%;left:3%;color:#5599ff;opacity:0.1;font-size:3.5rem;">◆</div>
        <div class="float-anim3 glow-blink" style="position:absolute;top:70%;right:15%;color:#f0c030;opacity:0.1;font-size:1.8rem;">✦</div>
        <div class="float-anim" style="position:absolute;top:38%;right:3%;color:#ff5533;opacity:0.1;font-size:2rem;">◇</div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="ff-box" style="width:100%;max-width:460px;padding:3rem 2.5rem;text-align:center;margin:1.5rem;">

        {{-- Logo Crystal --}}
        <div class="float-anim" style="display:flex;justify-content:center;margin-bottom:1.8rem;">
            <svg width="52" height="60" viewBox="0 0 32 36" fill="none" aria-hidden="true">
                <polygon points="16,1 6,12 16,15" fill="#ffe880" opacity="0.95"/>
                <polygon points="16,1 26,12 16,15" fill="#c08820" opacity="0.85"/>
                <polygon points="6,12 16,15 16,31"  fill="#8a5c10"/>
                <polygon points="26,12 16,15 16,31" fill="#d4931c"/>
                <polygon points="16,1 26,12 16,31 6,12" fill="none" stroke="#f0c030" stroke-width="0.9"/>
                {{-- inner glow lines --}}
                <line x1="16" y1="1"  x2="16" y2="15" stroke="#fff8d0" stroke-width="0.3" opacity="0.4"/>
                <line x1="6"  y1="12" x2="26" y2="12" stroke="#fff8d0" stroke-width="0.3" opacity="0.4"/>
            </svg>
        </div>

        {{-- Title --}}
        <h1 class="ff-title" style="font-size:1.9rem;margin-bottom:0.4rem;">FFXIV Market</h1>
        <p class="ff-subtitle">Analyzer</p>

        <div class="ff-divider" style="margin:1.6rem 0;"></div>

        {{-- Elemental Crystals (FF5 reference) --}}
        <div style="display:flex;justify-content:center;gap:1.5rem;margin-bottom:1.8rem;">
            <div class="crystal-el float-anim2">
                <svg width="22" height="26" viewBox="0 0 22 26" fill="none">
                    <polygon points="11,1 4,10 11,12.5 18,10" fill="#5599ff" opacity="0.8"/>
                    <polygon points="4,10 11,12.5 11,24"  fill="#3366bb" opacity="0.7"/>
                    <polygon points="18,10 11,12.5 11,24" fill="#4488dd" opacity="0.75"/>
                    <polygon points="11,1 18,10 11,24 4,10" fill="none" stroke="#88bbff" stroke-width="0.7"/>
                </svg>
                <span style="color:#3a5080;">Water</span>
            </div>
            <div class="crystal-el float-anim3">
                <svg width="22" height="26" viewBox="0 0 22 26" fill="none">
                    <polygon points="11,1 4,10 11,12.5 18,10" fill="#ff7733" opacity="0.85"/>
                    <polygon points="4,10 11,12.5 11,24"  fill="#cc4411" opacity="0.7"/>
                    <polygon points="18,10 11,12.5 11,24" fill="#ee6622" opacity="0.75"/>
                    <polygon points="11,1 18,10 11,24 4,10" fill="none" stroke="#ffaa66" stroke-width="0.7"/>
                </svg>
                <span style="color:#602a10;">Fire</span>
            </div>
            <div class="crystal-el float-anim">
                <svg width="22" height="26" viewBox="0 0 22 26" fill="none">
                    <polygon points="11,1 4,10 11,12.5 18,10" fill="#44cc66" opacity="0.85"/>
                    <polygon points="4,10 11,12.5 11,24"  fill="#228844" opacity="0.7"/>
                    <polygon points="18,10 11,12.5 11,24" fill="#33aa55" opacity="0.75"/>
                    <polygon points="11,1 18,10 11,24 4,10" fill="none" stroke="#88ee99" stroke-width="0.7"/>
                </svg>
                <span style="color:#1a5030;">Wind</span>
            </div>
            <div class="crystal-el float-anim4">
                <svg width="22" height="26" viewBox="0 0 22 26" fill="none">
                    <polygon points="11,1 4,10 11,12.5 18,10" fill="#cc8833" opacity="0.85"/>
                    <polygon points="4,10 11,12.5 11,24"  fill="#885511" opacity="0.7"/>
                    <polygon points="18,10 11,12.5 11,24" fill="#aa6622" opacity="0.75"/>
                    <polygon points="11,1 18,10 11,24 4,10" fill="none" stroke="#eebb66" stroke-width="0.7"/>
                </svg>
                <span style="color:#502a08;">Earth</span>
            </div>
        </div>

        {{-- Tagline --}}
        <p style="font-size:0.78rem;color:#4a5470;margin-bottom:2rem;line-height:1.6;">
            Analise oportunidades de crafting em tempo real.<br>
            Dados do Universalis &amp; XIVAPI.
        </p>

        {{-- Action Buttons --}}
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            @auth
                <a href="{{ route('market.dashboard') }}" class="ff-btn-enter">
                    <span style="font-size:0.55rem;">►</span> Entrar no Market Board
                </a>
            @else
                <a href="{{ route('login') }}" class="ff-btn-enter">
                    <span style="font-size:0.55rem;">►</span> Iniciar Sessão
                </a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="ff-btn-secondary">
                        Criar Conta
                    </a>
                @endif
            @endauth
        </div>

        {{-- FF7 Mako separator --}}
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #252560;">
            <p style="font-family:'Share Tech Mono',monospace;font-size:0.55rem;color:#1e2448;letter-spacing:0.2em;">
                MAKO ENERGY CORP. · EORZEA SECTOR · v1.0
            </p>
        </div>
    </div>

    {{-- Version/credits --}}
    <p style="font-family:'Share Tech Mono',monospace;font-size:0.5rem;color:#141830;letter-spacing:0.2em;margin-top:1.2rem;">
        ✦ FINAL FANTASY XIV MARKET ANALYZER ✦
    </p>
</body>
</html>
