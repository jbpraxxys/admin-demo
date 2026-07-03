<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class DemoFileController extends Controller
{
    public function show(string $slug, string $path)
    {
        $project = Project::where('slug', $slug)->first();

        if (!$project) {
            abort(404);
        }

        if ($project->status === 'archived') {
            abort(404);
        }

        // Check if user is logged in as admin/owner - they get direct access
        $isAdminOrOwner = auth()->check() && (auth()->user()->role === 'admin' || $project->user_id === auth()->id());
        
        // Check demo password session
        $hasDemoAuth = Session::get("demo_auth_{$slug}", false);

        if (!$isAdminOrOwner && !$hasDemoAuth) {
            // Store intended URL so login can redirect back after auth
            Session::put("demo_intended_{$slug}", "/projects/{$slug}/{$path}");
            // Redirect to demo login page
            return redirect("/projects/{$slug}/login");
        }

        // Try active public path first, then archived storage path
        $publicPath = public_path("projects/{$slug}/{$path}");
        $archivedPath = storage_path("app/archived/{$slug}/{$path}");

        if (File::exists($publicPath)) {
            $fullPath = $publicPath;
        } elseif (File::exists($archivedPath)) {
            $fullPath = $archivedPath;
        } else {
            abort(404);
        }

        $mimeType = File::mimeType($fullPath);
        $content = File::get($fullPath);

        // For HTML files, inject protection script
        if (str_contains($mimeType, 'html') || str_ends_with(strtolower($path), '.html') || str_ends_with(strtolower($path), '.htm')) {
            $content = $this->injectProtectionScript($content);
        }

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function injectProtectionScript(string $html): string
    {
        $script = <<<'SCRIPT'
<script>
(function() {
    // Disable right-click
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    }, true);

    // Disable keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+S / Cmd+S
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            return false;
        }
        // Ctrl+U / Cmd+U (view source)
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            return false;
        }
        // F12 (dev tools)
        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+I / Cmd+Option+I (inspect element)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'I') {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+J / Cmd+Option+J (console)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'J') {
            e.preventDefault();
            return false;
        }
        // Ctrl+Shift+C / Cmd+Shift+C (inspect element)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
            e.preventDefault();
            return false;
        }
    }, true);

    // Disable text selection and drag
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });

    // Prevent dev tools detection bypass
    setInterval(function() {
        if (window.outerHeight - window.innerHeight > 200 || window.outerWidth - window.innerWidth > 200) {
            document.body.innerHTML = '';
        }
    }, 1000);
})();
</script>
SCRIPT;

        // Try to inject before </body>
        if (str_contains($html, '</body>')) {
            $html = str_replace('</body>', $script . "\n</body>", $html);
        } elseif (str_contains($html, '</html>')) {
            // Inject before </html> if no </body>
            $html = str_replace('</html>', $script . "\n</html>", $html);
        } else {
            // Append if no closing tags
            $html .= "\n" . $script;
        }

        return $html;
    }
}
