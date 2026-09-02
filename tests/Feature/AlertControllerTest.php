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

    public function test_guest_is_redirected_to_login_from_alerts_index(): void
    {
        $response = $this->get('/alerts');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_an_alert(): void
    {
        $user   = User::factory()->create();
        $item   = Item::create(['id' => 'craft-item', 'name' => 'Craft Item']);
        $server = Server::create(['name' => 'Balmung', 'slug' => 'balmung', 'datacenter' => 'Crystal', 'region' => 'NA']);

        $response = $this->actingAs($user)->post('/alerts', [
            'item_id'    => $item->id,
            'server_id'  => $server->id,
            'min_profit' => 5000,
            'min_margin' => 20,
        ]);

        $response->assertRedirect(route('alerts.index'));
        $this->assertDatabaseHas('alerts', ['user_id' => $user->id, 'item_id' => $item->id]);
    }

    public function test_user_cannot_toggle_another_users_alert(): void
    {
        $owner  = User::factory()->create();
        $intruder = User::factory()->create();
        $alert  = $this->createAlert($owner);

        $response = $this->actingAs($intruder)->patch("/alerts/{$alert->id}/toggle");

        $response->assertForbidden();
        $this->assertTrue($alert->fresh()->is_active);
    }

    public function test_user_cannot_delete_another_users_alert(): void
    {
        $owner    = User::factory()->create();
        $intruder = User::factory()->create();
        $alert    = $this->createAlert($owner);

        $response = $this->actingAs($intruder)->delete("/alerts/{$alert->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('alerts', ['id' => $alert->id]);
    }

    public function test_owner_can_delete_their_own_alert(): void
    {
        $user  = User::factory()->create();
        $alert = $this->createAlert($user);

        $response = $this->actingAs($user)->delete("/alerts/{$alert->id}");

        $response->assertRedirect(route('alerts.index'));
        $this->assertDatabaseMissing('alerts', ['id' => $alert->id]);
    }
}
