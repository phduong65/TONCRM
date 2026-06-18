<?php

namespace App\Http\Requests;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChannelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platform'            => ['required', Rule::in(Channel::PLATFORMS)],
            'platform_channel_id' => ['required', 'string', 'max:255'],
            'name'                => ['required', 'string', 'max:255'],
            'access_token'        => ['required', 'string'],
            'webhook_secret'      => ['nullable', 'string'],
        ];
    }
}
