<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookService
{
    public function sendMessage($channel, $contact, string $message): void
    {
        $platformId = data_get($contact->platform_ids, 'facebook');
        if (!$platformId) return;

        Http::withToken($channel->access_token)
            ->post("https://graph.facebook.com/v19.0/me/messages", [
                'recipient' => ['id' => $platformId],
                'message'   => ['text' => $message],
            ]);
    }
}
