<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_conversations' => Conversation::where('tenant_id', $tenantId)->count(),
            'open_conversations'  => Conversation::where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'total_contacts'      => Contact::where('tenant_id', $tenantId)->count(),
            'total_channels'      => Channel::where('tenant_id', $tenantId)->where('is_active', true)->count(),
            'messages_today'      => Message::whereHas('conversation', fn($q) => $q->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())->count(),
        ];

        $recentConversations = Conversation::with(['contact', 'channel'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->orderByDesc('last_message_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentConversations'));
    }
}
