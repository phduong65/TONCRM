<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'      => ['nullable', 'string', 'max:255'],
            'content'    => ['required', 'string'],
            'source_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
