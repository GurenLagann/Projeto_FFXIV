<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FFXIV Market') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cinzel+Decorative:wght@700&family=Figtree:wght@400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles

    <style>
        :root {
            --bg:       #06060f;
            --surface:  #0b0b1e;
            --card:     #0f0f28;
            --border:   #252560;
            --hi:       #4040a0;   /* structural: borders, brackets — no contrast req */
            --hi-label: #7878c8;   /* text use — 5.1:1 on --bg — WCAG AA */
            --crystal:  #5599ff;
            --mako:     #00dd77;
            --gold:     #f0c030;
            --fire:     #ff5533;
            --text:     #ccd4f0;
            --dim:      #7280a0;   /* 5.1:1 on --bg — WCAG AA */
        }

        html, body { height: 100%; }

        body {
            background-color: var(--bg);
            background-image: radial-gradient(ellipse 70% 50% at 50% -5%, rgba(85,153,255,0.08) 0%, transparent 60%);
            color: var(--text);
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        :focus-visible { outline: 1px solid var(--crystal); outline-offset: 2px; }

        /* ── FF Window Box ── */
        .ff-box {
            background: rgba(9, 9, 24, 0.98);
            border: 1px solid var(--border);
            box-shadow:
                inset 0 0 0 1px rgba(37,37,96,0.5),
                0 12px 60px rgba(0,0,0,0.85);
            position: relative;
        }
        .ff-box::before,
        .ff-box::after {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            border-color: var(--hi);
            border-style: solid;
            pointer-events: none;
        }
        .ff-box::before { top:  5px; left:  5px; border-width: 1px 0 0 1px; }
        .ff-box::after  { bottom: 5px; right: 5px; border-width: 0 1px 1px 0; }

        /* ── Typography ── */
        .ff-title {
            font-family: 'Cinzel Decorative', 'Cinzel', Georgia, serif;
            background: linear-gradient(135deg, #c08820 0%, #f0c030 40%, #ffe880 65%, #c08820 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.08em;
        }

        .ff-label {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.6rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--hi-label); /* 5.1:1 on --bg — WCAG AA */
            display: block;
            margin-bottom: 0.4rem;
        }

        /* ── Input ── */
        .ff-input {
            width: 100%;
            background: rgba(4, 4, 14, 0.98);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            border-radius: 0;
            transition: border-color 120ms, box-shadow 120ms;
        }
        .ff-input:focus {
            outline: none;
            border-color: var(--crystal);
            box-shadow: 0 0 0 1px rgba(85,153,255,0.12), inset 0 0 10px rgba(85,153,255,0.04);
        }
        .ff-input::placeholder { color: #252840; }

        /* ── Button ── */
        .ff-btn {
            width: 100%;
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.72rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            background: linear-gradient(160deg, #141440 0%, #1e1e60 100%);
            border: 1px solid var(--gold);
            color: var(--gold);
            padding: 0.7rem 1.5rem;
            cursor: pointer;
            transition: background 120ms, box-shadow 120ms, color 120ms, border-color 120ms;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .ff-btn::before { content: '►'; font-size: 0.55rem; }
        .ff-btn:hover {
            background: linear-gradient(160deg, #1e1e60 0%, #28288a 100%);
            box-shadow: 0 0 24px rgba(240,192,48,0.2);
            color: #ffe880;
            border-color: #ffe880;
        }

        .ff-link {
            font-family: 'Cinzel', Georgia, serif;
            font-size: 0.6rem;
            letter-spacing: 0.08em;
            color: var(--dim);
            transition: color 120ms;
            text-decoration: none;
        }
        .ff-link:hover { color: var(--crystal); }

        .ff-error {
            font-size: 0.7rem;
            color: var(--fire);
            letter-spacing: 0.04em;
            margin-top: 0.25rem;
        }

        .ff-success-msg {
            font-size: 0.72rem;
            color: var(--mako);
            letter-spacing: 0.05em;
            padding: 0.5rem 0.75rem;
            background: rgba(0,30,15,0.6);
            border: 1px solid rgba(0,221,119,0.25);
            margin-bottom: 1rem;
        }

        /* ── Checkbox ── */
        .ff-checkbox {
            appearance: none;
            width: 14px;
            height: 14px;
            background: rgba(4,4,14,0.95);
            border: 1px solid var(--border);
            position: relative;
            cursor: pointer;
            flex-shrink: 0;
            border-radius: 0;
        }
        .ff-checkbox:checked { border-color: var(--crystal); background: rgba(85,153,255,0.15); }
        .ff-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: -1px;
            left: 1px;
            font-size: 10px;
            color: var(--crystal);
        }

        /* ── Floating crystals (decorative) ── */
        @keyframes crystal-drift {
            0%   { transform: translateY(0) rotate(0deg);   opacity: 0.35; }
            50%  { transform: translateY(-14px) rotate(180deg); opacity: 0.6; }
            100% { transform: translateY(0) rotate(360deg); opacity: 0.35; }
        }
        .c-float  { animation: crystal-drift 7s  ease-in-out infinite; }
        .c-float2 { animation: crystal-drift 9s  ease-in-out infinite reverse; animation-delay: 1.5s; }
        .c-float3 { animation: crystal-drift 11s ease-in-out infinite; animation-delay: 3s; }
    </style>
</head>
<body>
    {{-- Floating crystals (background atmosphere) --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none select-none" aria-hidden="true">
        <span class="c-float  absolute text-[#5599ff] text-2xl opacity-20" style="top:12%;left:7%;">◆</span>
        <span class="c-float2 absolute text-[#f0c030] text-3xl opacity-15" style="top:22%;right:10%;">◇</span>
        <span class="c-float3 absolute text-[#00dd77] text-xl opacity-15" style="bottom:35%;left:12%;">◆</span>
        <span class="c-float  absolute text-[#a855f7] text-2xl opacity-[0.12]" style="bottom:22%;right:8%;">◇</span>
        <span class="c-float2 absolute text-[#5599ff] text-4xl opacity-[0.08]" style="top:65%;left:4%;">◆</span>
        <span class="c-float3 absolute text-[#f0c030] text-xl opacity-10" style="top:75%;right:18%;">✦</span>
    </div>

    {{-- Logo --}}
    <div class="mb-8 text-center">
        <div class="flex justify-center mb-3">
            <svg width="36" height="40" viewBox="0 0 32 36" fill="none" aria-hidden="true">
                <polygon points="16,1 6,12 16,15" fill="#ffe880" opacity="0.95"/>
                <polygon points="16,1 26,12 16,15" fill="#c08820" opacity="0.85"/>
                <polygon points="6,12 16,15 16,31"  fill="#8a5c10"/>
                <polygon points="26,12 16,15 16,31" fill="#d4931c"/>
                <polygon points="16,1 26,12 16,31 6,12" fill="none" stroke="#f0c030" stroke-width="0.9"/>
            </svg>
        </div>
        <h1 class="ff-title text-xl">FFXIV Market</h1>
        <p style="font-family:'Cinzel',serif;font-size:0.5rem;letter-spacing:0.22em;color:#2a3060;text-transform:uppercase;margin-top:0.3rem;">
            Eorzea Market Board Analyzer
        </p>
    </div>

    {{-- Auth Card --}}
    <div class="ff-box w-full max-w-sm px-8 py-8">
        {{ $slot }}
    </div>

    <p class="mt-6" style="font-family:'Share Tech Mono',monospace;font-size:0.5rem;color:#1e244a;letter-spacing:0.2em;">
        ✦ POWERED BY UNIVERSALIS & XIVAPI ✦
    </p>

    @livewireScripts
</body>
</html>
