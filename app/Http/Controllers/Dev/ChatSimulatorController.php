<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookJob;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatSimulatorController extends Controller
{
    public function index(): View
    {
        $channels = Channel::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('platform')
            ->get();

        return view('dev.chat', compact('channels'));
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'channel_id'  => ['required', 'uuid', 'exists:channels,id'],
            'sender_name' => ['required', 'string', 'max:100'],
            'content'     => ['required', 'string', 'max:2000'],
        ]);

        $channel = Channel::where('id', $request->channel_id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Deterministic sender ID per name+platform — conversations persist across sends
        $senderId = 'sim_' . substr(md5($request->sender_name . $channel->platform), 0, 12);

        // dispatchSync = chạy ngay, không qua queue — phù hợp cho dev/test
        ProcessWebhookJob::dispatchSync(
            payload: [
                'sender_id'   => $senderId,
                'sender_name' => $request->sender_name,
                'type'        => 'text',
                'content'     => $request->content,
            ],
            platform:  $channel->platform,
            channelId: $channel->platform_channel_id,
        );

        $contact = Contact::where('tenant_id', $channel->tenant_id)
            ->where('platform_ids->' . $channel->platform, $senderId)
            ->first();

        $conversation = $contact
            ? Conversation::where('channel_id', $channel->id)
                ->where('contact_id', $contact->id)
                ->latest()
                ->first()
            : null;

        return response()->json([
            'ok'               => true,
            'conversation_id'  => $conversation?->id,
            'conversation_url' => $conversation ? route('conversations.show', $conversation->id) : null,
            'contact_name'     => $contact?->display_name ?? $request->sender_name,
            'channel_name'     => $channel->name,
            'platform'         => $channel->platform,
            'sent_at'          => now()->format('H:i:s'),
        ]);
    }
}
