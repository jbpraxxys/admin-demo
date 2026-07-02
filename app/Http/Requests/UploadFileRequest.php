<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    private const ALLOWED_EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'json',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico',
        'woff', 'woff2', 'ttf',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $extensions = implode(',', self::ALLOWED_EXTENSIONS);
        $maxKb = (int) env('MAX_UPLOAD_SIZE', 51200);

        return [
            'files' => ['required', 'array'],
            'files.*' => ["mimes:{$extensions}", "max:{$maxKb}"],
        ];
    }
}
