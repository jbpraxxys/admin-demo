<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\HtpasswdService;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function __construct(private HtpasswdService $htpasswd) {}

    public function index()
    {
        $projects = Project::with('creator')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => array_merge($p->toArray(), [
                'shareable_link' => $p->shareable_link,
                'creator_name' => $p->creator->name,
            ]));

        return inertia('Admin/Projects/Index', ['projects' => $projects]);
    }

    public function destroy(Project $project)
    {
        $this->htpasswd->delete($project->slug);

        $dir = public_path("projects/{$project->slug}");
        if (is_dir($dir)) {
            array_map('unlink', glob("{$dir}/*") ?: []);
            rmdir($dir);
        }

        $project->delete();

        return redirect('/admin/projects');
    }
}
