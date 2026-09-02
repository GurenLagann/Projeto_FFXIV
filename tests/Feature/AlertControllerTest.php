<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Item;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAlert(User $user): Alert
    {
        $item = Item::create(['id' => 'test-item-1', 'name' => 'Test Item']);
        $server = Server::create([
            'name' => 'Test Server', 'slug' => 'test-server',
            'datacenter' => 'Test DC', 'region' => 'NA',
        ]);

        return Alert::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'server_id' => $server->id,
            'min_profit' => 100,
            'min_margin' => 10,
        ]);
    }

    public function test_alerts_edit_route_does_not_exist(): void
    {
        $user = User::factory()->create();
        $alert = $this->createAlert($user);

        $response = $this->actingAs($user)->get("/alerts/{$alert->id}/edit");

        $response->assertNotFound();
    }

    public function test_alerts_update_route_does_not_exist(): void
    {
        $user = User::factory()->create();
        $alert = $this->createAlert($user);

        $response = $this->actingAs($user)->put("/alerts/{$alert->id}", []);

        $response->assertMethodNotAllowed();
    }
}
