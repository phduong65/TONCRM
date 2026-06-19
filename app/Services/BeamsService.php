<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeamsService
{
    private string $instanceId;
    private string $secretKey;

    public function __construct()
    {
        $this->instanceId = config('services.pusher_beams.instance_id', '');
        $this->secretKey  = config('services.pusher_beams.secret_key', '');
    }

    public function notifyTenant(string $tenantId, string $title, string $body, string $path = '/conversations'): void
    {
        if (!$this->instanceId || !$this->secretKey) return;

        $interest = 'tenant-' . $tenantId;
        $endpoint = "https://{$this->instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$this->instanceId}/publishes/interests";

        try {
            Http::withToken($this->secretKey)
                ->post($endpoint, [
                    'interests' => [$interest],
                    'web' => [
                        'notification' => [
                            'title'     => $title,
                            'body'      => $body,
                            'icon'      => '/favicon.ico',
                            'deep_link' => rtrim(config('app.url'), '/') . $path,
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Pusher Beams notification failed: ' . $e->getMessage());
        }
    }
}
