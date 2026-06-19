<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->message->conversation->tenant_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        $msg     = $this->message;
        $conv    = $msg->conversation;
        $contact = $conv->contact;
        $channel = $conv->channel;

        $palette = ['#f43f5e','#ec4899','#a855f7','#6366f1','#3b82f6','#0ea5e9','#10b981','#84cc16','#f59e0b','#f97316'];
        $platformHex = ['facebook' => '#1877F2', 'zalo' => '#0068FF', 'instagram' => '#E1306C', 'tiktok' => '#111111', 'webchat' => '#6366F1'];
        $platformBg  = ['facebook' => '#EBF3FF', 'zalo' => '#E5EEFF', 'instagram' => '#FEE9F0', 'tiktok' => '#F3F3F3', 'webchat' => '#EDEEFF'];

        $name        = $contact->display_name;
        $platform    = $channel->platform;
        $avatarColor = $palette[abs(crc32($name ?? 'K')) % count($palette)];

        return [
            'message' => [
                'id'              => $msg->id,
                'conversation_id' => $msg->conversation_id,
                'sender_type'     => $msg->sender_type,
                'sender_id'       => $msg->sender_id,
                'message_type'    => $msg->message_type,
                'content'         => $msg->content,
                'created_at'      => $msg->created_at?->toISOString(),
            ],
            'conversation_id' => $msg->conversation_id,
            'meta' => [
                'url'           => route('conversations.show', $conv->id),
                'contact_name'  => $name,
                'initial'       => mb_strtoupper(mb_substr($name, 0, 1)),
                'avatar_color'  => $avatarColor,
                'platform'      => $platform,
                'platform_abbr' => strtoupper(substr($platform, 0, 2)),
                'platform_hex'  => $platformHex[$platform] ?? '#6B7280',
                'platform_bg'   => $platformBg[$platform]  ?? '#F3F4F6',
                'status'        => $conv->status,
                'is_ai_active'  => $conv->is_ai_active,
            ],
        ];
    }
}
