<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    // Instagram dùng Messenger API for Instagram — cùng endpoint với Facebook
    private string $graphApiBase = 'https://graph.facebook.com/v18.0';

    public function sendMessage(Channel $channel, Contact $contact, string $text): void
    {
        $recipientId = $contact->platform_ids['instagram'] ?? null;

        if (!$recipientId) {
            Log::warning('InstagramService: missing instagram platform_id', [
                'contact_id' => $contact->id,
            ]);
            return;
        }

        $response = Http::withToken($channel->access_token)
            ->post("{$this->graphApiBase}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message'   => ['text' => $text],
            ]);

        if (!$response->successful()) {
            Log::error('InstagramService sendMessage failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }
    }
}
