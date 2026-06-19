<?php

namespace App\Jobs;

use App\Events\MessageReceived;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\BeamsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly array $payload,
        public readonly string $platform,
        public readonly string $channelId,
    ) {}

    public function handle(): void
    {
        $platformMsgId = $this->payload['mid'] ?? $this->payload['message_id'] ?? null;
        if ($platformMsgId && Message::where('platform_message_id', $platformMsgId)->exists()) {
            return;
        }

        $channel = Channel::where('platform', $this->platform)
            ->where('platform_channel_id', $this->channelId)
            ->where('is_active', true)
            ->first();

        if (!$channel) return;

        $platformIds = [$this->platform => $this->payload['sender_id']];

        $contact = Contact::firstOrCreate(
            ['tenant_id' => $channel->tenant_id, 'platform_ids->' . $this->platform => $this->payload['sender_id']],
            [
                'tenant_id'    => $channel->tenant_id,
                'name'         => $this->payload['sender_name'] ?? null,
                'platform_ids' => $platformIds,
            ]
        );

        $conversation = Conversation::firstOrCreate(
            ['channel_id' => $channel->id, 'contact_id' => $contact->id, 'status' => 'open'],
            ['tenant_id' => $channel->tenant_id, 'is_ai_active' => true]
        );

        $message = Message::create([
            'conversation_id'     => $conversation->id,
            'sender_type'         => 'customer',
            'sender_id'           => $this->payload['sender_id'],
            'message_type'        => $this->payload['type'] ?? 'text',
            'content'             => $this->payload['content'],
            'payload'             => $this->payload,
            'platform_message_id' => $platformMsgId,
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new MessageReceived($message->load('conversation')));

        app(BeamsService::class)->notifyTenant(
            tenantId: $channel->tenant_id,
            title:    'Tin nhắn mới — ' . ($contact->display_name ?? 'Khách hàng'),
            body:     mb_substr($message->content, 0, 100),
            path:     '/conversations/' . $conversation->id,
        );

        if ($conversation->is_ai_active) {
            dispatch(new InvokeAiAgentJob($conversation));
        }
    }
}
