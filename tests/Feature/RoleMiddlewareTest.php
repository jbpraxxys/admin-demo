<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin/projects');
        $response->assertStatus(200);
    }

    public function test_pm_cannot_access_admin_route(): void
    {
        $pm = User::factory()->create(['role' => 'pm']);
        $response = $this->actingAs($pm)->get('/admin/projects');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
