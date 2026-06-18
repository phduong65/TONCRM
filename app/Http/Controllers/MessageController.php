<?php

namespace App\Http\Controllers;

use App\Events\MessageReceived;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\FacebookService;
use App\Services\TikTokService;
use App\Services\ZaloService;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function store(StoreMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->tenant_id === auth()->user()->tenant_id, 403);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'staff',
            'sender_id'       => (string) auth()->id(),
            'message_type'    => 'text',
            'content'         => $request->validated('content'),
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'is_ai_active'    => false,
        ]);

        $channel = $conversation->channel;
        $service = match($channel->platform) {
            'facebook' => app(FacebookService::class),
            'zalo'     => app(ZaloService::class),
            'tiktok'   => app(TikTokService::class),
            default    => null,
        };
        $service?->sendMessage($channel, $conversation->contact, $request->validated('content'));

        event(new MessageReceived($message->load('conversation')));

        activity()->causedBy(auth()->user())->performedOn($conversation)->log('replied');

        return back()->with('success', 'Đã gửi tin nhắn.');
    }
}
