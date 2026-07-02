<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(2)->create(['role' => 'pm']);

        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users', 3)
        );
    }

    public function test_admin_can_create_pm_account(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'New PM',
            'email' => 'pm@praxxys.ph',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'role' => 'pm',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'pm@praxxys.ph', 'role' => 'pm']);
    }

    public function test_admin_can_delete_user(): void
    {
        $pm = User::factory()->create(['role' => 'pm']);

        $this->actingAs($this->admin)->delete("/admin/users/{$pm->id}");

        $this->assertDatabaseMissing('users', ['id' => $pm->id]);
    }
}
