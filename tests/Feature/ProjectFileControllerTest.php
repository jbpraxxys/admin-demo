<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectFileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $pm;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pm = User::factory()->create(['role' => 'pm']);
        $this->project = Project::factory()->create(['created_by' => $this->pm->id, 'slug' => 'file-test-project']);

        mkdir(public_path("projects/{$this->project->slug}"), 0755, true);
    }

    protected function tearDown(): void
    {
        $dir = public_path("projects/{$this->project->slug}");
        if (is_dir($dir)) {
            array_map('unlink', glob("{$dir}/*"));
            rmdir($dir);
        }
        parent::tearDown();
    }

    public function test_pm_can_upload_html_file(): void
    {
        $file = UploadedFile::fake()->create('index.html', 10, 'text/html');

        $response = $this->actingAs($this->pm)
            ->post("/projects/{$this->project->slug}/files", ['files' => [$file]]);

        $response->assertRedirect("/projects/{$this->project->slug}");
        $this->assertDatabaseHas('project_files', [
            'project_id' => $this->project->id,
            'filename' => 'index.html',
        ]);
    }

    public function test_uploaded_file_exists_on_disk(): void
    {
        $file = UploadedFile::fake()->create('index.html', 10, 'text/html');

        $this->actingAs($this->pm)
            ->post("/projects/{$this->project->slug}/files", ['files' => [$file]]);

        $this->assertFileExists(public_path("projects/{$this->project->slug}/index.html"));
    }

    public function test_executable_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('shell.php', 5, 'application/x-php');

        $response = $this->actingAs($this->pm)
            ->post("/projects/{$this->project->slug}/files", ['files' => [$file]]);

        $response->assertSessionHasErrors('files.0');
    }

    public function test_pm_can_delete_file(): void
    {
        $fileRecord = ProjectFile::create([
            'project_id' => $this->project->id,
            'filename' => 'index.html',
            'path' => "projects/{$this->project->slug}/index.html",
            'size' => 100,
            'uploaded_by' => $this->pm->id,
        ]);
        touch(public_path($fileRecord->path));

        $response = $this->actingAs($this->pm)
            ->delete("/projects/{$this->project->slug}/files/{$fileRecord->id}");

        $response->assertRedirect("/projects/{$this->project->slug}");
        $this->assertDatabaseMissing('project_files', ['id' => $fileRecord->id]);
    }
}
