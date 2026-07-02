<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Auth;

class ProjectFileController extends Controller
{
    public function store(UploadFileRequest $request, string $slug)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $destination = public_path("projects/{$project->slug}");
            $file->move($destination, $filename);

            ProjectFile::updateOrCreate(
                ['project_id' => $project->id, 'filename' => $filename],
                [
                    'path' => "projects/{$project->slug}/{$filename}",
                    'size' => filesize("{$destination}/{$filename}"),
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now(),
                ]
            );
        }

        return redirect("/projects/{$project->slug}");
    }

    public function destroy(string $slug, int $file)
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $file = ProjectFile::where('id', $file)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $fullPath = public_path($file->path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $file->delete();

        return redirect("/projects/{$project->slug}");
    }
}
