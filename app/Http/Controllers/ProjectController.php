<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\HtpasswdService;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct(private HtpasswdService $htpasswd) {}

    public function index()
    {
        $projects = Project::where('created_by', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => array_merge($p->toArray(), [
                'shareable_link' => $p->shareable_link,
            ]));

        return inertia('Dashboard', ['projects' => $projects]);
    }

    public function create()
    {
        return inertia('Projects/Create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        if (!is_dir(public_path("projects/{$project->slug}"))) {
            mkdir(public_path("projects/{$project->slug}"), 0755, true);
        }
        $this->htpasswd->write($project->slug, $project->demo_password);

        return redirect("/projects/{$project->slug}");
    }

    public function show(string $slug)
    {
        $project = $this->findProject($slug);

        $files = $project->files()->orderBy('uploaded_at', 'desc')->get();

        return inertia('Projects/Show', [
            'project' => array_merge($project->toArray(), [
                'shareable_link' => $project->shareable_link,
            ]),
            'files' => $files,
        ]);
    }

    public function update(UpdateProjectRequest $request, string $slug)
    {
        $project = $this->findProject($slug);

        $project->update($request->validated());
        $this->htpasswd->write($project->slug, $project->demo_password);

        return redirect("/projects/{$project->slug}");
    }

    public function destroy(string $slug)
    {
        $project = $this->findProject($slug);

        $this->htpasswd->delete($project->slug);
        $this->rmdirRecursive(public_path("projects/{$project->slug}"));
        $project->delete();

        return redirect('/dashboard');
    }

    private function findProject(string $slug): Project
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        if ($project->created_by !== Auth::id()) {
            abort(403);
        }

        return $project;
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = "{$dir}/{$item}";
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }

        rmdir($dir);
    }
}
