<?php

namespace Tests\Feature;

use App\Clients\LodestoneClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LodestoneClientTest extends TestCase
{
    private const LODESTONE_ID = 12345678;

    private function fakePage(string $html, int $status = 200): void
    {
        Http::fake([
            'na.finalfantasyxiv.com/lodestone/character/*' => Http::response($html, $status),
        ]);
    }

    private function fixtureHtml(): string
    {
        return <<<HTML
            <p class="frame__chara__name">Test Character</p>
            <p class="frame__chara__world">Balmung [Crystal]</p>
            <div class="frame__chara__face"><img src="https://img.finalfantasyxiv.com/avatar.jpg" /></div>
            <div class="character__selfintroduction">Hello, verification code: FFXIV-ABC12345</div>
            <div class="character__level__list">tanks and healers</div>
            <div class="character__level__list">dps jobs</div>
            <div class="character__level__list"><li><img data-tooltip="Carpenter">10</li><li><img data-tooltip="Blacksmith">42</li><li><img data-tooltip="Armorer">-</li></div>
            <div class="character__level__list">gatherers</div>
            HTML;
    }

    public function test_get_character_info_parses_name_server_and_avatar(): void
    {
        $this->fakePage($this->fixtureHtml());

        $info = (new LodestoneClient())->getCharacterInfo(self::LODESTONE_ID);

        $this->assertNotNull($info);
        $this->assertSame('Test Character', $info['Name']);
        $this->assertSame('Balmung', $info['Server']);
        $this->assertSame('https://img.finalfantasyxiv.com/avatar.jpg', $info['Avatar']);
    }

    public function test_get_character_info_returns_null_when_name_cannot_be_found(): void
    {
        $this->fakePage('<html><body>not a character page</body></html>');

        $info = (new LodestoneClient())->getCharacterInfo(self::LODESTONE_ID);

        $this->assertNull($info);
    }

    public function test_get_character_info_returns_null_on_http_failure(): void
    {
        $this->fakePage('Service Unavailable', 503);

        $info = (new LodestoneClient())->getCharacterInfo(self::LODESTONE_ID);

        $this->assertNull($info);
    }

    public function test_get_verification_data_parses_bio_and_crafting_levels(): void
    {
        $this->fakePage($this->fixtureHtml());

        $data = (new LodestoneClient())->getVerificationData(self::LODESTONE_ID);

        $this->assertNotNull($data);
        $this->assertStringContainsString('FFXIV-ABC12345', $data['bio']);
        $this->assertSame([8 => 10, 9 => 42], $data['craftingLevels']);
    }

    public function test_get_verification_data_returns_null_when_page_is_unreachable(): void
    {
        $this->fakePage('', 500);

        $data = (new LodestoneClient())->getVerificationData(self::LODESTONE_ID);

        $this->assertNull($data);
    }

    public function test_get_bio_returns_decoded_text(): void
    {
        $this->fakePage($this->fixtureHtml());

        $bio = (new LodestoneClient())->getBio(self::LODESTONE_ID);

        $this->assertSame('Hello, verification code: FFXIV-ABC12345', $bio);
    }
}
