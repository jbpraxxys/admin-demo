<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
        if ($this->isDemoAuthValid($slug)) {
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
                $this->grantDemoAuth($slug);
                $redirectTo = $intended ?: $fallbackRedirect;
                Session::forget("demo_intended_{$slug}");
                return redirect($redirectTo);
            }
        } else {
            // Check htpasswd file
            $contents = trim(file_get_contents($htpasswdPath));
            [, $hash] = explode(':', $contents, 2);
            
            if ($this->verifyPassword($request->password, $hash)) {
                $this->grantDemoAuth($slug);
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
        Cookie::queue(Cookie::forget('demo_session_' . $slug));
        return redirect("/projects/{$slug}/login");
    }

    /**
     * Grant demo authentication for a project.
     * Stores structured auth data with timestamp and session ID,
     * and sets a browser session cookie (expires on browser close).
     */
    private function grantDemoAuth(string $slug): void
    {
        Session::put("demo_auth_{$slug}", [
            'authenticated_at' => time(),
            'session_id' => Session::getId(),
        ]);

        // Set a session cookie (expires when browser closes)
        Cookie::queue('demo_session_' . $slug, 'active', 0);
    }

    /**
     * Check if demo authentication is still valid.
     * Validates:
     * 1. Browser session cookie exists (browser was not closed)
     * 2. Session token matches (session was not regenerated)
     * 3. Less than 24 hours have passed since authentication
     */
    private function isDemoAuthValid(string $slug): bool
    {
        $authData = Session::get("demo_auth_{$slug}");

        if (!is_array($authData)) {
            return false;
        }

        // 1. Check if browser session cookie exists (browser was closed)
        if (!request()->cookie('demo_session_' . $slug)) {
            Session::forget("demo_auth_{$slug}");
            return false;
        }

        // 2. Check if session token matches (handles session regeneration)
        $currentSessionId = Session::getId();
        if (empty($authData['session_id']) || $authData['session_id'] !== $currentSessionId) {
            Session::forget("demo_auth_{$slug}");
            return false;
        }

        // 3. Check if 24 hours have passed since authentication
        $authenticatedAt = $authData['authenticated_at'] ?? null;
        if (!$authenticatedAt || (time() - $authenticatedAt) > 86400) {
            Session::forget("demo_auth_{$slug}");
            return false;
        }

        return true;
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
