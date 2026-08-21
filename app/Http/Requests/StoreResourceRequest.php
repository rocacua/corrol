<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['required', 'in:public,private'],
            'game' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            
            'file' => ['required_without:external_url', 'nullable', 'file', 'max:102400'],
            'external_url' => ['required_without:file', 'nullable', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del recurso es obligatorio.',
            'file.required_without' => 'Debes seleccionar un archivo o indicar una URL externa.',
            'external_url.required_without' => 'Debes indicar una URL externa o subir un archivo.',
            'file.max' => 'El archivo no puede superar los 100MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (Auth::guest()) {
            $this->merge([
                'privacy' => 'public',
            ]);
        }
    }
}