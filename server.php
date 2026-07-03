<?php

/**
 * Development server router for PHP's built-in web server (used by
 * `php artisan serve`).
 *
 * PHP's built-in server resolves each incoming URL against the filesystem to
 * choose a "script" and sets SCRIPT_NAME / PATH_INFO accordingly. This project
 * stores client demo files directly inside the document root under
 * public/projects/{slug}/ (e.g. public/projects/demo/index.html). Because those
 * are real files, PHP picks one of them as the script and splits the rest of
 * the URL into PATH_INFO, e.g. for DELETE /projects/demo/files/6 PHP sets:
 *
 *     SCRIPT_NAME = /projects/demo/index.html
 *     PATH_INFO   = /files/6
 *
 * Laravel derives the request path from SCRIPT_NAME + REQUEST_URI, so this
 * corruption makes routes such as /projects/{slug}/files/{file} fail to match
 * and return 404 — even though the route is registered and the controller
 * works (the feature test passes).
 *
 * This router therefore:
 *   1. Serves genuine static files directly (return false), so client demo
 *      files at /projects/{slug}/<file> are still streamed as-is.
 *   2. For every other request, normalises the server variables so Laravel
 *      always sees a clean request rooted at /index.php and routes purely off
 *      REQUEST_URI.
 *
 * Production is unaffected: Apache/nginx rewrite to index.php directly and the
 * PATH_INFO splitting below never happens.
 */

$publicPath = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve real static files directly; let PHP's built-in server stream them.
// Skip files under /projects/ so Laravel routes handle them (injects protection scripts).
if ($uri !== '/' && is_file($publicPath.$uri)) {
    if (str_starts_with($uri, '/projects/')) {
        // Fall through to Laravel routing below
    } else {
        return false;
    }
}

// Normalise the server variables so Laravel computes the request path purely
// from REQUEST_URI, undoing any PATH_INFO splitting PHP may have performed
// against files that exist under public/ (e.g. public/projects/{slug}/index.html).
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicPath.'/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['PATH_INFO'] = '';
unset($_SERVER['PATH_TRANSLATED']);

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] $uri\n");
require_once $publicPath.'/index.php';

