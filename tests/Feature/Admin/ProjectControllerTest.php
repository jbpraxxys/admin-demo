<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_sees_all_projects(): void
    {
        Project::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/projects');

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects', 3)
        );
    }

    public function test_admin_can_delete_any_project(): void
    {
        $project = Project::factory()->create(['slug' => 'admin-del-test']);

        $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
