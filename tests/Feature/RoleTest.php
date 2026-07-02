<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_attribute(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pm = User::factory()->create(['role' => 'pm']);

        $this->assertEquals('admin', $admin->role);
        $this->assertEquals('pm', $pm->role);
    }

    public function test_has_role_returns_true_for_matching_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('pm'));
    }

    public function test_has_role_accepts_multiple_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->hasRole('admin', 'pm'));
    }
}
