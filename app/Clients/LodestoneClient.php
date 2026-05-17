<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;

class LodestoneClient
{
    private const BASE = 'https://na.finalfantasyxiv.com/lodestone/character/';
    private const UA   = 'Mozilla/5.0 (X11; Linux x86_64; rv:124.0) Gecko/20100101 Firefox/124.0';

    // Lodestone tooltip name → FFXIV ClassJob ID (matches XIVAPI / controller CRAFT_JOB_IDS)
    private const CRAFTER_JOB_IDS = [
        'Carpenter'     => 8,
        'Blacksmith'    => 9,
        'Armorer'       => 10,
        'Goldsmith'     => 11,
        'Leatherworker' => 12,
        'Weaver'        => 13,
        'Alchemist'     => 14,
        'Culinarian'    => 15,
    ];

    private function fetchPage(int $lodestoneId): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent'      => self::UA,
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'DNT'             => '1',
                ])
                ->get(self::BASE . $lodestoneId . '/');

            if ($response->ok()) {
                return $response->body();
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function parseBio(string $html): string
    {
        if (preg_match('/<div[^>]*class="[^"]*character__selfintroduction[^"]*"[^>]*>([\s\S]*?)<\/div>/i', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        return '';
    }

    /**
     * Returns [classJobId => level] for all 8 crafting jobs.
     * Scrapes the 3rd character__level__list block (index 2) which always contains crafters.
     * Returns empty array if the block cannot be found.
     */
    private function parseCraftingLevels(string $html): array
    {
        preg_match_all(
            '/<div[^>]*class="character__level__list"[^>]*>([\s\S]*?)<\/div>/i',
            $html,
            $blocks
        );

        // Block index 2 = crafters (0=tanks+healers, 1=dps, 2=crafters, 3=gatherers)
        $crafterBlock = $blocks[1][2] ?? null;
        if (!$crafterBlock) {
            return [];
        }

        // Each <li> is: <img ... data-tooltip="JobName">LEVEL (or -)
        preg_match_all(
            '/data-tooltip="([^"]+)">(\d+|-)/i',
            $crafterBlock,
            $entries,
            PREG_SET_ORDER
        );

        $levels = [];
        foreach ($entries as $entry) {
            $jobName = $entry[1];
            $level   = $entry[2];

            if (isset(self::CRAFTER_JOB_IDS[$jobName]) && $level !== '-') {
                $levels[self::CRAFTER_JOB_IDS[$jobName]] = (int) $level;
            }
        }

        return $levels;
    }

    /**
     * Used during verify(): returns bio + crafting levels in a single HTTP request.
     * Returns null if the page cannot be fetched.
     */
    public function getVerificationData(int $lodestoneId): ?array
    {
        $html = $this->fetchPage($lodestoneId);
        if (!$html) {
            return null;
        }

        return [
            'bio'           => $this->parseBio($html),
            'craftingLevels'=> $this->parseCraftingLevels($html),
        ];
    }

    /**
     * Used during byId(): returns name, server, avatar + bio in a single HTTP request.
     */
    public function getCharacterInfo(int $lodestoneId): ?array
    {
        $html = $this->fetchPage($lodestoneId);
        if (!$html) {
            return null;
        }

        $name = null;
        if (preg_match('/<p[^>]*class="[^"]*frame__chara__name[^"]*"[^>]*>([\s\S]*?)<\/p>/i', $html, $m)) {
            $name = trim(strip_tags($m[1]));
        }

        if (!$name) {
            return null;
        }

        $server = null;
        if (preg_match('/<p[^>]*class="[^"]*frame__chara__world[^"]*"[^>]*>([\s\S]*?)<\/p>/i', $html, $m)) {
            $raw    = trim(strip_tags($m[1]));
            $server = trim(preg_replace('/\s*\[.*?\]/', '', $raw));
        }

        $avatar = null;
        if (preg_match('/<div[^>]*class="[^"]*frame__chara__face[^"]*"[^>]*>[\s\S]*?<img[^>]+src="([^"]+)"/i', $html, $m)) {
            $avatar = $m[1];
        }

        return [
            'ID'     => $lodestoneId,
            'Name'   => $name,
            'Server' => $server ?? '—',
            'Avatar' => $avatar ?? '',
            'Bio'    => $this->parseBio($html),
        ];
    }

    /** Kept for compatibility — single bio fetch. */
    public function getBio(int $lodestoneId): ?string
    {
        $html = $this->fetchPage($lodestoneId);
        if (!$html) {
            return null;
        }

        return $this->parseBio($html);
    }
}
