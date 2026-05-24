<?php

namespace App\Livewire;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Throwable;

class HealthMonitor extends Component
{
    public array   $apis      = [];
    public array   $system    = [];
    public ?string $checkedAt = null;

    /**
     * Checks are NOT run on mount — wire:init triggers after the first render
     * so the page appears instantly with a loading state, then fills in.
     */
    public function runChecks(): void
    {
        $this->apis   = [];
        $this->system = [];

        // ── 1. External APIs (parallel via Http::pool) ──────────────────────
        $poolStart = microtime(true);

        try {
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('universalis')
                    ->timeout(8)
                    ->withHeaders(['User-Agent' => 'FFXIV-MarketAnalyzer/1.0'])
                    ->get('https://universalis.app/api/v2/worlds'),

                $pool->as('xivapi')
                    ->timeout(8)
                    ->withHeaders(['User-Agent' => 'FFXIV-MarketAnalyzer/1.0'])
                    ->get('https://v2.xivapi.com/api/sheet/Item?limit=1&fields=Name'),

                $pool->as('lodestone')
                    ->timeout(8)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:124.0) Gecko/20100101 Firefox/124.0'])
                    ->get('https://na.finalfantasyxiv.com/lodestone/'),
            ]);
        } catch (Throwable) {
            $responses = [];
        }

        $poolMs = (int) round((microtime(true) - $poolStart) * 1000);

        $this->apis = [
            $this->processPoolResponse(
                response: $responses['universalis'] ?? null,
                name:     'Universalis',
                poolMs:   $poolMs,
                cacheTtl: '5 min',
                endpoint: 'universalis.app/api/v2',
                purpose:  'Preços de mercado em tempo real',
            ),
            $this->processPoolResponse(
                response: $responses['xivapi'] ?? null,
                name:     'XIVAPI',
                poolMs:   $poolMs,
                cacheTtl: '24 h',
                endpoint: 'v2.xivapi.com/api/sheet',
                purpose:  'Receitas, itens e dados do jogo',
            ),
            $this->processPoolResponse(
                response: $responses['lodestone'] ?? null,
                name:     'Lodestone',
                poolMs:   $poolMs,
                cacheTtl: '—',
                endpoint: 'finalfantasyxiv.com/lodestone',
                purpose:  'Dados de personagem (scraping HTML)',
                fragile:  true,
            ),
        ];

        // ── 2. Sistema (local — rápido, sequencial) ──────────────────────────
        $this->system = [
            $this->checkDatabase(),
            $this->checkCache(),
            $this->checkQueue(),
        ];

        $this->checkedAt = now()->format('H:i:s');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function processPoolResponse(
        mixed  $response,
        string $name,
        int    $poolMs,
        string $cacheTtl,
        string $endpoint,
        string $purpose,
        bool   $fragile = false,
    ): array {
        $base = [
            'name'     => $name,
            'cacheTtl' => $cacheTtl,
            'endpoint' => $endpoint,
            'purpose'  => $purpose,
            'fragile'  => $fragile,
            'ms'       => $poolMs,   // pool time is the best estimate we have
        ];

        // Pool item is an exception when the request failed
        if ($response instanceof Throwable || $response === null) {
            $msg = $response instanceof Throwable
                ? (str_contains(strtolower($response->getMessage()), 'timed') ? 'Timeout' : 'Inacessível')
                : 'Sem resposta';

            return array_merge($base, ['status' => 'down', 'message' => $msg]);
        }

        if ($response->successful()) {
            $status  = $poolMs > 4000 ? 'degraded' : 'up';
            $message = $poolMs > 4000 ? 'Resposta lenta' : 'Operacional';
        } else {
            $status  = 'degraded';
            $message = 'HTTP ' . $response->status();
        }

        return array_merge($base, compact('status', 'message'));
    }

    private function checkDatabase(): array
    {
        $driver = config('database.default', 'mysql');

        try {
            $counts = [
                'items'           => DB::table('items')->count(),
                'recipes'         => DB::table('recipes')->count(),
                'gathering_items' => DB::table('gathering_items')->count(),
                'analyses'        => DB::table('analyses')->count(),
                'alerts'          => DB::table('alerts')->count(),
            ];

            $detail = implode(' · ', [
                number_format($counts['items'])           . ' itens',
                number_format($counts['recipes'])         . ' receitas',
                number_format($counts['gathering_items']) . ' coletáveis',
                number_format($counts['analyses'])        . ' análises',
                number_format($counts['alerts'])          . ' alertas',
            ]);

            return [
                'name'    => 'Banco de Dados',
                'icon'    => '🗄',
                'status'  => 'up',
                'message' => 'Conectado',
                'detail'  => $detail,
                'badge'   => strtoupper($driver),
            ];
        } catch (Throwable $e) {
            return [
                'name'    => 'Banco de Dados',
                'icon'    => '🗄',
                'status'  => 'down',
                'message' => 'Erro de conexão',
                'detail'  => $e->getMessage(),
                'badge'   => strtoupper($driver),
            ];
        }
    }

    private function checkCache(): array
    {
        $driver = config('cache.default', 'file');

        try {
            $key = 'health:ping:' . uniqid();
            Cache::put($key, 'pong', 10);
            $hit = Cache::get($key) === 'pong';
            Cache::forget($key);

            return [
                'name'    => 'Cache',
                'icon'    => '⚡',
                'status'  => $hit ? 'up' : 'degraded',
                'message' => $hit ? 'Leitura/escrita OK' : 'Falha na leitura',
                'detail'  => 'Driver: ' . strtoupper($driver),
                'badge'   => strtoupper($driver),
            ];
        } catch (Throwable $e) {
            return [
                'name'    => 'Cache',
                'icon'    => '⚡',
                'status'  => 'down',
                'message' => 'Erro',
                'detail'  => $e->getMessage(),
                'badge'   => strtoupper($driver),
            ];
        }
    }

    private function checkQueue(): array
    {
        $driver = config('queue.default', 'sync');

        if ($driver === 'sync') {
            return [
                'name'    => 'Fila (Queue)',
                'icon'    => '📋',
                'status'  => 'up',
                'message' => 'Sync — sem worker necessário',
                'detail'  => 'Jobs processados de forma síncrona (ambiente local)',
                'badge'   => 'SYNC',
            ];
        }

        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();

            return [
                'name'    => 'Fila (Queue)',
                'icon'    => '📋',
                'status'  => $failed > 0 ? 'degraded' : 'up',
                'message' => $failed > 0
                    ? "{$failed} job(s) com falha"
                    : "{$pending} job(s) pendente(s)",
                'detail'  => "Driver: " . strtoupper($driver)
                    . " · {$pending} pendentes · {$failed} com falha",
                'badge'   => strtoupper($driver),
            ];
        } catch (Throwable) {
            return [
                'name'    => 'Fila (Queue)',
                'icon'    => '📋',
                'status'  => 'up',
                'message' => 'Operacional',
                'detail'  => 'Driver: ' . strtoupper($driver),
                'badge'   => strtoupper($driver),
            ];
        }
    }

    public function render()
    {
        return view('livewire.health-monitor');
    }
}
