<?php

namespace Tests\Feature;

use App\Jobs\ProcessWebhookJob;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Tenant;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    private function makeChannel(string $channelId = 'fb-page-123'): Channel
    {
        $tenant = Tenant::create(['name' => 'WH Test', 'slug' => 'wh-test-' . $channelId, 'plan' => 'starter']);

        return Channel::create([
            'tenant_id'           => $tenant->id,
            'platform'            => 'facebook',
            'platform_channel_id' => $channelId,
            'name'                => 'FB Page',
            'access_token'        => 'token',
            'webhook_secret'      => 'test-secret',
        ]);
    }

    public function test_facebook_webhook_verification_get(): void
    {
        config(['services.facebook.verify_token' => 'mytoken123']);

        $response = $this->get('/api/webhooks/facebook?' . http_build_query([
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => 'mytoken123',
            'hub_challenge'    => 'CHALLENGE_XYZ',
        ]));

        $response->assertOk()->assertSee('CHALLENGE_XYZ');
    }

    public function test_facebook_webhook_rejects_invalid_signature(): void
    {
        config(['services.facebook.app_secret' => 'real-secret']);

        $rawBody = json_encode(['entry' => []]);

        $response = $this->call(
            'POST',
            '/api/webhooks/facebook',
            [],
            [],
            [],
            [
                'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalidsignature',
                'CONTENT_TYPE'             => 'application/json',
            ],
            $rawBody
        );

        $response->assertForbidden();
    }

    public function test_facebook_webhook_dispatches_job_with_valid_signature(): void
    {
        Queue::fake();

        $this->makeChannel('fb-page-123');
        config(['services.facebook.app_secret' => 'real-secret']);
        config(['services.facebook.verify_token' => 'mytoken']);

        $rawBody = json_encode([
            'entry' => [[
                'id'        => 'fb-page-123',
                'messaging' => [[
                    'sender'  => ['id' => 'user-456'],
                    'message' => ['mid' => 'msg-001', 'text' => 'Hello'],
                ]],
            ]],
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, 'real-secret');

        $response = $this->call(
            'POST',
            '/api/webhooks/facebook',
            [],
            [],
            [],
            [
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
                'CONTENT_TYPE'             => 'application/json',
            ],
            $rawBody
        );

        $response->assertOk();
        Queue::assertPushed(ProcessWebhookJob::class);
    }

    public function test_duplicate_message_is_ignored(): void
    {
        $channel = $this->makeChannel('fb-page-dup');
        $tenant  = $channel->tenant;

        $contact = Contact::create(['tenant_id' => $tenant->id, 'name' => 'Dup User']);
        $conv    = Conversation::create([
            'tenant_id'  => $tenant->id,
            'channel_id' => $channel->id,
            'contact_id' => $contact->id,
        ]);

        Message::create([
            'conversation_id'     => $conv->id,
            'sender_type'         => 'customer',
            'sender_id'           => 'user-456',
            'content'             => 'Hello',
            'platform_message_id' => 'msg-dup-001',
        ]);

        $job = new ProcessWebhookJob(
            payload: [
                'sender_id'   => 'user-456',
                'type'        => 'text',
                'content'     => 'Hello again',
                'mid'         => 'msg-dup-001',
                'sender_name' => null,
            ],
            platform:  'facebook',
            channelId: 'fb-page-dup',
        );

        $job->handle();

        $this->assertDatabaseCount('messages', 1);
    }
}
