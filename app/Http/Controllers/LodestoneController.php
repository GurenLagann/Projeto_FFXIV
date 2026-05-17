<?php

namespace App\Http\Controllers;

use App\Clients\LodestoneClient;
use App\Clients\XIVApiClient;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LodestoneController extends Controller
{
    // Crafting job IDs in FFXIV (ClassJob IDs from XIVAPI)
    private const CRAFT_JOB_IDS = [8, 9, 10, 11, 12, 13, 14, 15];

    public function show()
    {
        $servers = Server::orderBy('region')->orderBy('name')->get();
        return view('lodestone.link', compact('servers'));
    }

    public function search(Request $request, XIVApiClient $client)
    {
        $request->validate([
            'character_name'   => 'required|string|min:2|max:50',
            'character_server' => 'required|string',
        ]);

        $results = $client->searchCharacters(
            $request->character_name,
            $request->character_server
        );

        if ($results === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'search' => 'A busca via XIVAPI está temporariamente indisponível (Lodestone protegido). Use o campo "Entrar ID manualmente" abaixo.',
                ]);
        }

        return view('lodestone.search-results', compact('results'));
    }

    /**
     * Manual entry: user provides their numeric Lodestone character ID.
     * We try to fetch info directly from the Lodestone page as fallback.
     */
    public function byId(Request $request, LodestoneClient $lodestone)
    {
        $request->validate([
            'lodestone_id' => 'required|integer|min:1|max:99999999',
        ]);

        $id   = (int) $request->lodestone_id;
        $info = $lodestone->getCharacterInfo($id);

        $code = 'FFXIV-' . strtoupper(Str::random(8));

        $request->user()->update([
            'lodestone_id'          => $id,
            'character_name'        => $info['Name']   ?? "Personagem #{$id}",
            'character_server'      => $info['Server'] ?? '—',
            'character_avatar'      => $info['Avatar'] ?? null,
            'verification_code'     => $code,
            'character_verified_at' => null,
        ]);

        $character = $info['Name'] ?? "Personagem #{$id}";

        return view('lodestone.verify', compact('code', 'character'));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'lodestone_id'    => 'required|integer',
            'character_name'  => 'required|string',
            'character_server'=> 'required|string',
            'character_avatar'=> 'nullable|string',
        ]);

        $code = 'FFXIV-' . strtoupper(Str::random(8));

        $request->user()->update([
            'lodestone_id'          => $request->lodestone_id,
            'character_name'        => $request->character_name,
            'character_server'      => $request->character_server,
            'character_avatar'      => $request->character_avatar,
            'verification_code'     => $code,
            'character_verified_at' => null,
        ]);

        return view('lodestone.verify', ['code' => $code, 'character' => $request->character_name]);
    }

    public function verify(Request $request, XIVApiClient $xivapi, LodestoneClient $lodestone)
    {
        $user = $request->user();

        if (!$user->lodestone_id || !$user->verification_code) {
            return back()->withErrors(['error' => 'Nenhum personagem pendente de verificação.']);
        }

        $id = (int) $user->lodestone_id;

        // Try XIVAPI first (may return null if Lodestone is protected)
        $xivapiData    = $xivapi->getCharacter($id);
        $lodestoneData = null;

        if ($xivapiData) {
            $bio       = $xivapiData['Character']['Bio'] ?? '';
            $jobLevels = $this->extractJobLevelsFromXivapi($xivapiData);
        } else {
            // Fallback: fetch bio + crafting levels in one direct Lodestone request
            $lodestoneData = $lodestone->getVerificationData($id);

            if ($lodestoneData === null) {
                return back()->withErrors([
                    'error' => 'Não foi possível acessar o perfil na Lodestone. Verifique sua conexão e tente novamente em alguns minutos.',
                ]);
            }

            $bio       = $lodestoneData['bio'];
            $jobLevels = $lodestoneData['craftingLevels'];
        }

        if (!str_contains($bio, $user->verification_code)) {
            return back()->withErrors([
                'error' => 'Código não encontrado na bio. Certifique-se de salvar o perfil na Lodestone e aguarde alguns minutos antes de tentar novamente.',
            ]);
        }

        // Backfill avatar if missing (manual ID entry without avatar)
        $avatar = $user->character_avatar;
        if (!$avatar && $xivapiData) {
            $avatar = $xivapiData['Character']['Avatar'] ?? null;
        }

        $user->update([
            'character_verified_at' => now(),
            'verification_code'     => null,
            'job_levels'            => !empty($jobLevels) ? $jobLevels : null,
            'character_avatar'      => $avatar,
        ]);

        $msg = "Personagem {$user->character_name} verificado com sucesso!";
        if (!empty($jobLevels)) {
            $msg .= ' Níveis de crafting importados.';
        }

        return redirect()->route('lodestone.show')->with('success', $msg);
    }

    private function extractJobLevelsFromXivapi(array $data): array
    {
        $levels = [];
        foreach ($data['ClassJobs'] ?? [] as $job) {
            $id = $job['ClassID'] ?? null;
            if ($id && in_array($id, self::CRAFT_JOB_IDS)) {
                $levels[$id] = $job['Level'] ?? 0;
            }
        }
        return $levels;
    }

    public function unlink(Request $request)
    {
        $request->user()->update([
            'lodestone_id'          => null,
            'character_name'        => null,
            'character_server'      => null,
            'character_avatar'      => null,
            'verification_code'     => null,
            'character_verified_at' => null,
            'job_levels'            => null,
        ]);

        return redirect()->route('lodestone.show')
            ->with('success', 'Personagem desvinculado.');
    }
}
