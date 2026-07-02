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
            $this->removeDirectory($dir);
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

    public function test_executable_file_is_not_stored(): void
    {
        $file = UploadedFile::fake()->create('shell.php', 5, 'application/x-php');

        $this->actingAs($this->pm)
            ->post("/projects/{$this->project->slug}/files", ['files' => [$file]]);

        $this->assertFileDoesNotExist(public_path("projects/{$this->project->slug}/shell.php"));
        $this->assertDatabaseMissing('project_files', ['filename' => 'shell.php']);
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

    public function test_folder_upload_preserves_structure(): void
    {
        $index = UploadedFile::fake()->create('index.html', 1, 'text/html');
        $css = UploadedFile::fake()->create('style.css', 1, 'text/css');
        $js = UploadedFile::fake()->create('app.js', 1, 'application/javascript');

        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$index, $css, $js],
            'paths' => ['index.html', 'css/style.css', 'js/app.js'],
        ]);

        // Files land at the correct nested paths so relative links resolve.
        $this->assertFileExists(public_path("projects/{$this->project->slug}/index.html"));
        $this->assertFileExists(public_path("projects/{$this->project->slug}/css/style.css"));
        $this->assertFileExists(public_path("projects/{$this->project->slug}/js/app.js"));

        $this->assertDatabaseHas('project_files', [
            'project_id' => $this->project->id,
            'path' => "projects/{$this->project->slug}/css/style.css",
            'filename' => 'style.css',
        ]);
        $this->assertDatabaseHas('project_files', [
            'project_id' => $this->project->id,
            'path' => "projects/{$this->project->slug}/js/app.js",
            'filename' => 'app.js',
        ]);
    }

    public function test_flat_upload_still_works_without_paths(): void
    {
        $a = UploadedFile::fake()->create('a.html', 1, 'text/html');
        $b = UploadedFile::fake()->create('b.css', 1, 'text/css');

        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$a, $b],
        ]);

        $this->assertFileExists(public_path("projects/{$this->project->slug}/a.html"));
        $this->assertFileExists(public_path("projects/{$this->project->slug}/b.css"));
    }

    public function test_path_traversal_is_rejected(): void
    {
        $escaped = public_path('evil-' . uniqid() . '.html');
        @unlink($escaped);

        $file = UploadedFile::fake()->create('payload.html', 1, 'text/html');

        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$file],
            'paths' => ['../../' . basename($escaped)],
        ]);

        // Nothing escaped outside the project directory, and no record stored.
        $this->assertFileDoesNotExist($escaped);
        $this->assertDatabaseMissing('project_files', ['project_id' => $this->project->id]);
    }

    public function test_reuploading_same_path_overwrites(): void
    {
        $first = UploadedFile::fake()->create('index.html', 1, 'text/html');
        $second = UploadedFile::fake()->create('index.html', 1, 'text/html');

        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$first], 'paths' => ['index.html'],
        ]);
        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$second], 'paths' => ['index.html'],
        ]);

        $this->assertSame(
            1,
            ProjectFile::where('project_id', $this->project->id)
                ->where('path', "projects/{$this->project->slug}/index.html")
                ->count(),
            'Re-uploading the same path must overwrite, not duplicate.'
        );
    }

    public function test_os_junk_files_are_skipped(): void
    {
        $html = UploadedFile::fake()->create('index.html', 1, 'text/html');
        $junk = UploadedFile::fake()->create('.DS_Store', 1, 'application/octet-stream');

        $this->actingAs($this->pm)->post("/projects/{$this->project->slug}/files", [
            'files' => [$html, $junk],
            'paths' => ['index.html', '.DS_Store'],
        ]);

        $this->assertFileExists(public_path("projects/{$this->project->slug}/index.html"));
        $this->assertFileDoesNotExist(public_path("projects/{$this->project->slug}/.DS_Store"));
        $this->assertDatabaseMissing('project_files', ['filename' => '.DS_Store']);
    }

    public function test_deleting_nested_file_cleans_empty_parent_dirs(): void
    {
        $record = ProjectFile::create([
            'project_id' => $this->project->id,
            'filename' => 'style.css',
            'path' => "projects/{$this->project->slug}/css/style.css",
            'size' => 100,
            'uploaded_by' => $this->pm->id,
        ]);
        mkdir(public_path("projects/{$this->project->slug}/css"), 0755, true);
        touch(public_path($record->path));

        $this->actingAs($this->pm)
            ->delete("/projects/{$this->project->slug}/files/{$record->id}");

        $this->assertFileDoesNotExist(public_path("projects/{$this->project->slug}/css/style.css"));
        $this->assertDirectoryDoesNotExist(public_path("projects/{$this->project->slug}/css"));
        // Project root itself must survive.
        $this->assertDirectoryExists(public_path("projects/{$this->project->slug}"));
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = "{$dir}/{$item}";
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }

        @rmdir($dir);
    }
}
