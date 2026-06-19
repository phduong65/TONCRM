<?php

namespace App\Jobs;

use App\Events\MessageReceived;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiAgentService;
use App\Services\FacebookService;
use App\Services\InstagramService;
use App\Services\ZaloService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InvokeAiAgentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly Conversation $conversation) {}

    public function handle(AiAgentService $aiService): void
    {
        $response = $aiService->generateReply($this->conversation);
        if (!$response) return;

        $channel = $this->conversation->channel;
        $service = match($channel->platform) {
            'facebook'  => app(FacebookService::class),
            'instagram' => app(InstagramService::class),
            'zalo'      => app(ZaloService::class),
            default     => null,
        };

        $service?->sendMessage($channel, $this->conversation->contact, $response);

        $aiMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_type'     => 'ai_agent',
            'sender_id'       => 'ai',
            'message_type'    => 'text',
            'content'         => $response,
        ]);

        event(new MessageReceived($aiMessage->load('conversation')));
    }
}
