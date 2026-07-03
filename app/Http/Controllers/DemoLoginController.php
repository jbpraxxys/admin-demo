<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DemoLoginController extends Controller
{
    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)->first();

        if (!$project) {
            abort(404);
        }

        if ($project->status === 'archived') {
            abort(404);
        }

        // If already authenticated for this project, redirect to the first HTML file
        if (Session::get("demo_auth_{$slug}")) {
            $projectDir = public_path("projects/{$slug}");
            $files = glob("{$projectDir}/*.html");
            if (!empty($files)) {
                $firstFile = basename($files[0]);
                return redirect("/projects/{$slug}/{$firstFile}");
            }
            return redirect("/projects/{$slug}/login");
        }

        return inertia('Demo/Login', [
            'slug' => $slug,
            'projectName' => $project->name,
            'csrf_token' => csrf_token(),
        ]);
    }

    public function login(Request $request, string $slug)
    {
        $project = Project::where('slug', $slug)->first();

        if (!$project) {
            abort(404);
        }

        if ($project->status === 'archived') {
            abort(404);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Get intended redirect URL from session (set by DemoFileController)
        $intended = Session::get("demo_intended_{$slug}");

        // Verify password using htpasswd file
        $htpasswdPath = storage_path("htpasswd/{$slug}");
        $projectDir = public_path("projects/{$slug}");
        $htmlFiles = glob("{$projectDir}/*.html");
        $fallbackRedirect = !empty($htmlFiles) ? "/projects/{$slug}/" . basename($htmlFiles[0]) : "/projects/{$slug}/login";
        
        if (!file_exists($htpasswdPath)) {
            // Fallback: check against database directly
            if ($request->password === $project->demo_password) {
                Session::put("demo_auth_{$slug}", true);
                $redirectTo = $intended ?: $fallbackRedirect;
                Session::forget("demo_intended_{$slug}");
                return redirect($redirectTo);
            }
        } else {
            // Check htpasswd file
            $contents = trim(file_get_contents($htpasswdPath));
            [, $hash] = explode(':', $contents, 2);
            
            if ($this->verifyPassword($request->password, $hash)) {
                Session::put("demo_auth_{$slug}", true);
                $redirectTo = $intended ?: $fallbackRedirect;
                Session::forget("demo_intended_{$slug}");
                return redirect($redirectTo);
            }
        }

        return back()->withErrors([
            'password' => 'Incorrect demo password.',
        ]);
    }

    public function logout(string $slug)
    {
        Session::forget("demo_auth_{$slug}");
        return redirect("/projects/{$slug}/login");
    }

    private function verifyPassword(string $password, string $hash): bool
    {
        // Support {SHA} prefix
        if (str_starts_with($hash, '{SHA}')) {
            $expected = '{SHA}' . base64_encode(sha1($password, true));
            return hash_equals($expected, $hash);
        }

        // Support bcrypt (for future)
        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$')) {
            return password_verify($password, $hash);
        }

        return false;
    }
}
