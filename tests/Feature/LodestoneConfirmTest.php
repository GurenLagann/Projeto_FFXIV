<?php

namespace Tests\Feature;

use App\Clients\LodestoneClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LodestoneConfirmTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_uses_canonical_character_data_instead_of_trusting_the_request(): void
    {
        $user = User::factory()->create();

        $this->mock(LodestoneClient::class, function ($mock) {
            $mock->shouldReceive('getCharacterInfo')
                ->with(12345)
                ->once()
                ->andReturn([
                    'ID'     => 12345,
                    'Name'   => 'Real Character',
                    'Server' => 'Balmung',
                    'Avatar' => 'https://img.finalfantasyxiv.com/real.jpg',
                    'Bio'    => '',
                ]);
        });

        $this->actingAs($user)->post('/character/confirm', [
            'lodestone_id'     => 12345,
            'character_name'   => 'Spoofed Name',
            'character_server' => 'Spoofed Server',
            'character_avatar' => 'https://evil.example/avatar.jpg',
        ]);

        $user->refresh();

        $this->assertSame('Real Character', $user->character_name);
        $this->assertSame('Balmung', $user->character_server);
        $this->assertSame('https://img.finalfantasyxiv.com/real.jpg', $user->character_avatar);
    }
}
