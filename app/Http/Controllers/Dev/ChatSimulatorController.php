<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookJob;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatSimulatorController extends Controller
{
    public function index(): View
    {
        $channel = Channel::where('tenant_id', auth()->user()->tenant_id)
            ->where('platform', 'webchat')
            ->where('is_active', true)
            ->first();

        $conversation = null;
        $messages     = collect();

        if ($channel) {
            $conversation = Conversation::where('tenant_id', auth()->user()->tenant_id)
                ->where('channel_id', $channel->id)
                ->latest('last_message_at')
                ->first();

            if ($conversation) {
                $messages = $conversation->messages()->orderBy('created_at')->get();
            }
        }

        return view('dev.chat-simulator', compact('channel', 'conversation', 'messages'));
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'content'       => ['required', 'string', 'max:2000'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_id'   => ['nullable', 'string', 'max:100'],
        ]);

        $channel = Channel::where('tenant_id', auth()->user()->tenant_id)
            ->where('platform', 'webchat')
            ->where('is_active', true)
            ->firstOrFail();

        $customerId   = $request->input('customer_id') ?: ('dev-' . Str::slug($request->input('customer_name', 'khach-demo')));
        $customerName = $request->input('customer_name', 'Khách Demo');

        // Run synchronously so testing works without Horizon
        ProcessWebhookJob::dispatchSync(
            payload: [
                'sender_id'   => $customerId,
                'sender_name' => $customerName,
                'type'        => 'text',
                'content'     => $request->content,
            ],
            platform:  'webchat',
            channelId: $channel->platform_channel_id,
        );

        // Find the conversation to return its ID for Echo subscription
        $contact = Contact::where('tenant_id', auth()->user()->tenant_id)
            ->whereJsonContains('platform_ids->webchat', $customerId)
            ->first();

        $conversationId = null;
        if ($contact) {
            $conv = Conversation::where('channel_id', $channel->id)
                ->where('contact_id', $contact->id)
                ->latest()
                ->first();
            $conversationId = $conv?->id;
        }

        return response()->json([
            'ok'              => true,
            'conversation_id' => $conversationId,
            'sender_id'       => $customerId,
        ]);
    }
}
