@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Xin chào, {{ auth()->user()->name }}</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @php
        $statCards = [
            ['label' => 'Tổng hội thoại',    'value' => $stats['total_conversations'], 'color' => 'text-gray-900',   'bg' => 'bg-white'],
            ['label' => 'Đang mở',             'value' => $stats['open_conversations'],  'color' => 'text-green-600',  'bg' => 'bg-green-50'],
            ['label' => 'Liên hệ',             'value' => $stats['total_contacts'],       'color' => 'text-blue-600',   'bg' => 'bg-blue-50'],
            ['label' => 'Kênh hoạt động',      'value' => $stats['total_channels'],       'color' => 'text-purple-600', 'bg' => 'bg-purple-50'],
            ['label' => 'Tin nhắn hôm nay',   'value' => $stats['messages_today'],       'color' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
        ];
        @endphp

        @foreach($statCards as $card)
        <div class="rounded-xl border border-gray-100 {{ $card['bg'] }} p-5 shadow-sm">
            <p class="text-2xl font-bold {{ $card['color'] }}">{{ number_format($card['value']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Recent Conversations --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 text-sm">Hội thoại gần đây</h2>
            @can('view-conversations')
            <a href="{{ route('conversations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                Xem tất cả →
            </a>
            @endcan
        </div>

        @forelse($recentConversations as $conv)
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm shrink-0">
                {{ mb_strtoupper(mb_substr($conv->contact->display_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $conv->contact->display_name }}</p>
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $conv->channel->platform_color }} shrink-0">
                        {{ $conv->channel->platform_label }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $conv->last_message_at?->diffForHumans() ?? 'Chưa có tin' }}
                </p>
            </div>
            @can('view-conversations')
            <a href="{{ route('conversations.show', $conv) }}"
               class="text-xs text-indigo-600 hover:text-indigo-700 font-medium shrink-0">
                Xem →
            </a>
            @endcan
        </div>
        @empty
        <div class="text-center py-12 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="text-sm">Chưa có hội thoại nào</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
