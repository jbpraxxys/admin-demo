<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $pm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pm = User::factory()->create(['role' => 'pm']);
    }

    public function test_pm_sees_only_their_projects_on_dashboard(): void
    {
        $myProject = Project::factory()->create(['created_by' => $this->pm->id]);
        $otherProject = Project::factory()->create();

        $response = $this->actingAs($this->pm)->get('/dashboard');

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('projects', 1)
            ->where('projects.0.id', $myProject->id)
        );
    }

    public function test_pm_can_create_project(): void
    {
        $response = $this->actingAs($this->pm)->post('/projects', [
            'name' => 'Cabalen Demo',
            'client_name' => 'Cabalen',
            'slug' => 'cabalen-2026',
            'demo_password' => 'secret123',
        ]);

        $response->assertRedirect('/projects/cabalen-2026');
        $this->assertDatabaseHas('projects', [
            'slug' => 'cabalen-2026',
            'created_by' => $this->pm->id,
        ]);
    }

    public function test_creating_project_writes_htpasswd_file(): void
    {
        $this->actingAs($this->pm)->post('/projects', [
            'name' => 'Cabalen Demo',
            'client_name' => 'Cabalen',
            'slug' => 'cabalen-htpasswd-test',
            'demo_password' => 'secret123',
        ]);

        $this->assertFileExists(storage_path('htpasswd/cabalen-htpasswd-test'));
        unlink(storage_path('htpasswd/cabalen-htpasswd-test'));
    }

    public function test_slug_must_be_unique(): void
    {
        Project::factory()->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($this->pm)->post('/projects', [
            'name' => 'New Project',
            'client_name' => 'Client',
            'slug' => 'existing-slug',
            'demo_password' => 'pass',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_pm_can_update_project_password(): void
    {
        $project = Project::factory()->create(['created_by' => $this->pm->id]);

        $response = $this->actingAs($this->pm)->patch("/projects/{$project->slug}", [
            'demo_password' => 'newpassword',
        ]);

        $response->assertRedirect("/projects/{$project->slug}");
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'demo_password' => 'newpassword',
        ]);
    }

    public function test_pm_cannot_update_another_pms_project(): void
    {
        $otherProject = Project::factory()->create();

        $response = $this->actingAs($this->pm)->patch("/projects/{$otherProject->slug}", [
            'demo_password' => 'hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_pm_can_delete_their_project(): void
    {
        $project = Project::factory()->create(['created_by' => $this->pm->id, 'slug' => 'del-test']);

        $response = $this->actingAs($this->pm)->delete("/projects/{$project->slug}");

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
