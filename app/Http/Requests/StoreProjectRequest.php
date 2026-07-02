<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:projects,slug', 'regex:/^[a-z0-9_-]+$/'],
            'demo_password' => ['required', 'string', 'min:4', 'max:100'],
            'status' => ['required', 'in:active,archived'],
        ];
    }
}
