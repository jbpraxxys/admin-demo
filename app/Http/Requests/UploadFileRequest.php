<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Extensions permitted for demo uploads. Validated by extension rather
     * than MIME, because finfo frequently detects text-based web assets
     * (css/js/json/fonts) as "text/plain", which would wrongly reject them.
     * Static demo files are served by extension/URL, so this matches reality.
     */
    public const ALLOWED_EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'mjs', 'json', 'map',
        'txt', 'xml', 'webmanifest',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'avif',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Only structural validation here. Extension/size policy is enforced
        // in the controller as a soft skip, so a folder containing a stray
        // junk/disallowed file uploads the rest instead of failing the batch.
        return [
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file'],
            // Optional aligned array of relative paths (folder uploads). If
            // omitted, files are stored flat using their original name.
            'paths' => ['sometimes', 'array'],
            'paths.*' => ['nullable', 'string', 'max:500'],
        ];
    }
}
