<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $conversations = Conversation::with(['contact', 'channel'])
            ->where('tenant_id', $tenantId)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('channel_id'), fn($q) => $q->where('channel_id', $request->channel_id))
            ->when($request->filled('assigned'), function ($q) use ($request) {
                if ($request->assigned === 'me') {
                    return $q->where('assigned_user_id', auth()->id());
                }
                if ($request->assigned === 'unassigned') {
                    return $q->whereNull('assigned_user_id');
                }
            })
            ->orderByDesc('last_message_at')
            ->paginate(30)
            ->withQueryString();

        $channels  = Channel::where('tenant_id', $tenantId)->get();
        $staffList = User::where('tenant_id', $tenantId)->get();

        return view('conversations.index', compact('conversations', 'channels', 'staffList'));
    }

    public function show(Conversation $conversation): View
    {
        $this->authorizeTenant($conversation->tenant_id);

        $tenantId = auth()->user()->tenant_id;

        $conversations = Conversation::with(['contact', 'channel'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('last_message_at')
            ->paginate(30);

        $messages  = $conversation->messages()->with([])->orderBy('created_at')->get();
        $channels  = Channel::where('tenant_id', $tenantId)->get();
        $staffList = User::where('tenant_id', $tenantId)->get();

        return view('conversations.index', compact('conversations', 'channels', 'staffList', 'conversation', 'messages'));
    }

    public function toggleAi(Conversation $conversation): RedirectResponse
    {
        $this->authorizeTenant($conversation->tenant_id);
        $this->authorize('reply-conversations');

        $conversation->update(['is_ai_active' => !$conversation->is_ai_active]);

        $label = $conversation->is_ai_active ? 'bật' : 'tắt';
        return back()->with('success', "Đã {$label} AI Agent cho cuộc trò chuyện này.");
    }

    public function assign(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeTenant($conversation->tenant_id);
        $this->authorize('assign-conversations');

        $request->validate(['user_id' => ['nullable', 'exists:users,id']]);

        $conversation->update(['assigned_user_id' => $request->user_id]);
        activity()->causedBy(auth()->user())->performedOn($conversation)->log('assigned');

        return back()->with('success', 'Đã phân công nhân viên thành công.');
    }

    public function close(Conversation $conversation): RedirectResponse
    {
        $this->authorizeTenant($conversation->tenant_id);
        $this->authorize('reply-conversations');

        $conversation->update(['status' => 'closed']);
        activity()->causedBy(auth()->user())->performedOn($conversation)->log('closed');

        return redirect()->route('conversations.index')->with('success', 'Đã đóng cuộc trò chuyện.');
    }

    public function reopen(Conversation $conversation): RedirectResponse
    {
        $this->authorizeTenant($conversation->tenant_id);
        $this->authorize('reply-conversations');

        $conversation->update(['status' => 'open']);
        activity()->causedBy(auth()->user())->performedOn($conversation)->log('reopened');

        return back()->with('success', 'Đã mở lại cuộc trò chuyện.');
    }

    private function authorizeTenant(string $tenantId): void
    {
        abort_unless($tenantId === auth()->user()->tenant_id, 403);
    }
}
