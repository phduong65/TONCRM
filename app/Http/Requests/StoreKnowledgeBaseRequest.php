<?php

namespace App\Http\Requests;

use App\Models\KnowledgeBase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeBaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['nullable', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'source_url'  => ['nullable', 'url', 'max:500'],
            'source_type' => ['required', Rule::in(KnowledgeBase::SOURCE_TYPES)],
        ];
    }
}
