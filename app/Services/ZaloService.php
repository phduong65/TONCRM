<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZaloService
{
    public function sendMessage($channel, $contact, string $message): void
    {
        $platformId = data_get($contact->platform_ids, 'zalo');
        if (!$platformId) return;

        Http::withHeaders(['access_token' => $channel->access_token])
            ->post("https://openapi.zalo.me/v2.0/oa/message", [
                'recipient' => ['user_id' => $platformId],
                'message'   => ['text' => $message],
            ]);
    }
}
