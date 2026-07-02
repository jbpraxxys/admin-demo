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

        if ($project->status === 'archived') {
            abort(403, 'Cannot upload files to an archived project.');
        }

        $projectDir = public_path("projects/{$project->slug}");
        if (! is_dir($projectDir)) {
            mkdir($projectDir, 0755, true);
        }
        $rootReal = realpath($projectDir);

        $paths = $request->input('paths', []);
        $paths = is_array($paths) ? array_values($paths) : [];

        foreach ($request->file('files', []) as $index => $file) {
            // Policy: skip files that aren't an allowed asset type or exceed
            // the size cap. Skipping (not hard-failing) keeps folder uploads
            // working when one stray file is junk or disallowed.
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, UploadFileRequest::ALLOWED_EXTENSIONS, true)) {
                continue;
            }
            if ($file->getSize() > ((int) env('MAX_UPLOAD_SIZE', 51200)) * 1024) {
                continue;
            }

            $rawPath = $paths[$index] ?? $file->getClientOriginalName();
            $relative = $this->sanitizeRelativePath($rawPath);

            // Skip anything that doesn't reduce to a safe, traversal-free path
            // (e.g. "../../evil", absolute paths, OS junk).
            if ($relative === null) {
                continue;
            }

            $base = basename($relative);
            $subdir = dirname($relative);
            if (in_array($subdir, ['.', '/'], true)) {
                $subdir = '';
            }

            $destDir = $subdir === '' ? $projectDir : "{$projectDir}/{$subdir}";

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            // Defense in depth: never let a destination escape the project dir.
            $destReal = realpath($destDir);
            if (! $destReal || ! str_starts_with($destReal.DIRECTORY_SEPARATOR, $rootReal.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $file->move($destDir, $base);

            ProjectFile::updateOrCreate(
                ['project_id' => $project->id, 'path' => "projects/{$project->slug}/{$relative}"],
                [
                    'filename' => $base,
                    'size' => filesize("{$destDir}/{$base}"),
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

        if ($project->status === 'archived') {
            abort(403, 'Cannot modify files in an archived project.');
        }

        $file = ProjectFile::where('id', $file)
            ->where('project_id', $project->id)
            ->firstOrFail();

        $fullPath = public_path($file->path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $file->delete();

        $this->cleanupEmptyDirs(dirname($fullPath), $projectDir = public_path("projects/{$project->slug}"));

        return redirect("/projects/{$project->slug}");
    }

    /**
     * Reduce a client-supplied relative path to a safe, traversal-free path
     * inside the project directory. Returns null when the path should be
     * rejected entirely (absolute paths, ".." segments, control chars).
     */
    protected function sanitizeRelativePath(string $raw): ?string
    {
        $normalized = str_replace('\\', '/', $raw);

        // Reject absolute paths outright.
        if ($normalized !== '' && $normalized[0] === '/') {
            return null;
        }

        $clean = [];

        foreach (explode('/', $normalized) as $segment) {
            $segment = trim($segment);

            if ($segment === '' || $segment === '.') {
                continue;
            }

            // Traversal attempt -> reject the whole file.
            if ($segment === '..') {
                return null;
            }

            // Skip OS junk that folders frequently contain.
            if (in_array($segment, ['.DS_Store', 'Thumbs.db', '.git', '__MACOSX'], true)) {
                continue;
            }

            // Reject control characters and characters illegal in filenames.
            if (preg_match('#[\x00-\x1F\x7F<>:"|?*]#', $segment)) {
                return null;
            }

            $clean[] = $segment;
        }

        return $clean === [] ? null : implode('/', $clean);
    }

    /**
     * Remove now-empty parent directories left behind after a delete, stopping
     * at (and never removing) the project root.
     */
    protected function cleanupEmptyDirs(string $dir, string $projectRoot): void
    {
        $stopAt = realpath($projectRoot);

        while (is_dir($dir)) {
            $real = realpath($dir);

            if ($real === false || $real === $stopAt) {
                break;
            }

            // Don't traverse above the project root.
            if (! str_starts_with($real.DIRECTORY_SEPARATOR, $stopAt.DIRECTORY_SEPARATOR)) {
                break;
            }

            if (! empty(array_diff(scandir($dir), ['.', '..']))) {
                break; // directory still has content
            }

            if (! @rmdir($dir)) {
                break;
            }

            $dir = dirname($dir);
        }
    }
}
