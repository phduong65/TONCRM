<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChannelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'access_token'   => ['sometimes', 'string'],
            'webhook_secret' => ['nullable', 'string'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
