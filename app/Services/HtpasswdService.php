<?php

namespace App\Services;

class HtpasswdService
{
    public function write(string $slug, string $password): void
    {
        $dir = storage_path('htpasswd');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $hash = '{SHA}' . base64_encode(sha1($password, true));
        $content = "demo:{$hash}\n";
        file_put_contents(storage_path("htpasswd/{$slug}"), $content);
    }

    public function delete(string $slug): void
    {
        $path = storage_path("htpasswd/{$slug}");
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
