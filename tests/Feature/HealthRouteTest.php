<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_health_dashboard(): void
    {
        $response = $this->get('/health');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_health_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/health');

        $response->assertOk();
    }
}
