@extends('layouts.admin')
@section('title', 'Inbox')
@section('main-class', 'flex-1 overflow-hidden h-full')

@section('content')
@php
    $platformCfg = [
        'facebook'  => ['abbr' => 'FB', 'label' => 'Facebook',  'hex' => '#1877F2', 'bg' => '#EBF3FF'],
        'instagram' => ['abbr' => 'IG', 'label' => 'Instagram', 'hex' => '#E1306C', 'bg' => '#FEE9F0'],
        'zalo'      => ['abbr' => 'ZA', 'label' => 'Zalo OA',   'hex' => '#0068FF', 'bg' => '#E5EEFF'],
        'tiktok'    => ['abbr' => 'TT', 'label' => 'TikTok',    'hex' => '#111111', 'bg' => '#F3F3F3'],
        'webchat'   => ['abbr' => 'WC', 'label' => 'WebChat',   'hex' => '#6366F1', 'bg' => '#EDEEFF'],
    ];
    $palette = ['#f43f5e','#ec4899','#a855f7','#6366f1','#3b82f6','#0ea5e9','#10b981','#84cc16','#f59e0b','#f97316'];
    $ac = fn(?string $n) => $palette[abs(crc32($n ?? 'K')) % count($palette)];

    $currentStatus   = request('status', '');
    $currentChannel  = request('channel_id', '');
    $currentAssigned = request('assigned', '');

    $statusFilters = [
        ''            => 'Tất cả',
        'open'        => 'Đang mở',
        'pending'     => 'Chờ xử lý',
        'closed'      => 'Đã đóng',
    ];
    $assignFilters = [
        ''             => '',
        'me'           => 'Của tôi',
        'unassigned'   => 'Chưa giao',
    ];
@endphp

<div class="flex h-full">

    {{-- ════════════════════════════════ --}}
    {{-- PANEL 1 — CONVERSATION LIST     --}}
    {{-- ════════════════════════════════ --}}
    <aside class="w-[300px] flex flex-col border-r border-zinc-200 bg-white shrink-0">

        {{-- Header --}}
        <div class="px-4 pt-4 pb-3 border-b border-zinc-100 shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-semibold text-zinc-900">Hội thoại</h2>
                    <span class="px-1.5 py-0.5 rounded-md bg-zinc-100 text-zinc-500 text-xs font-medium tabular-nums">
                        {{ $conversations->total() }}
                    </span>
                </div>
                <button onclick="document.getElementById('conv-search-bar').classList.toggle('hidden')"
                        class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 transition-colors"
                        title="Tìm kiếm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>

            {{-- Search bar --}}
            <div id="conv-search-bar" class="hidden mb-3">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Tìm tên, điện thoại..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-zinc-50">
                </div>
            </div>

            {{-- Status filter --}}
            <div class="flex gap-1 flex-wrap">
                @foreach($statusFilters as $val => $label)
                <a href="{{ route('conversations.index', array_merge(request()->except(['status', 'page']), $val ? ['status' => $val] : [])) }}"
                   class="px-2.5 py-1 rounded-full text-xs font-medium transition-all duration-150
                          {{ $currentStatus === $val
                             ? 'bg-indigo-600 text-white shadow-sm'
                             : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-700' }}">
                    {{ $label }}
                </a>
                @endforeach
                @if($currentAssigned === 'me')
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Của tôi</span>
                @endif
            </div>

            {{-- Assignment quick filters --}}
            <div class="flex gap-1 mt-2">
                @foreach($assignFilters as $val => $label)
                @if($label)
                <a href="{{ route('conversations.index', array_merge(request()->except(['assigned', 'page']), $currentAssigned === $val ? [] : ['assigned' => $val])) }}"
                   class="px-2 py-0.5 rounded text-xs font-medium transition-colors
                          {{ $currentAssigned === $val ? 'text-indigo-600 bg-indigo-50' : 'text-zinc-400 hover:text-zinc-600' }}">
                    {{ $label }}
                </a>
                @endif
                @endforeach
            </div>

            {{-- Channel filter --}}
            @if($channels->count() > 0)
            <div class="flex gap-1 mt-2 overflow-x-auto scrollbar-none pb-0.5">
                <a href="{{ route('conversations.index', array_merge(request()->except(['channel_id', 'page']), [])) }}"
                   class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-all whitespace-nowrap
                          {{ !$currentChannel ? 'bg-zinc-800 text-white' : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200' }}">
                    Tất cả
                </a>
                @foreach($channels->groupBy('platform') as $platform => $chs)
                @php $cfg = $platformCfg[$platform] ?? ['abbr' => strtoupper(substr($platform,0,2)), 'label' => $platform, 'hex' => '#6B7280', 'bg' => '#F3F4F6']; @endphp
                @foreach($chs as $ch)
                <a href="{{ route('conversations.index', array_merge(request()->except(['channel_id', 'page']), ['channel_id' => $ch->id])) }}"
                   class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-all whitespace-nowrap"
                   style="{{ $currentChannel === $ch->id
                       ? 'background:'.$cfg['hex'].'; color:#fff;'
                       : 'background:'.$cfg['bg'].'; color:'.$cfg['hex'].';' }}">
                    <span class="font-bold">{{ $cfg['abbr'] }}</span>
                    <span class="opacity-80">{{ $ch->name }}</span>
                </a>
                @endforeach
                @endforeach
            </div>
            @endif
        </div>

        {{-- Conversation Items --}}
        <div class="flex-1 overflow-y-auto" id="conversation-list">
            @forelse($conversations as $conv)
            @php
                $isActive = isset($conversation) && $conversation->id === $conv->id;
                $cfg = $platformCfg[$conv->channel->platform] ?? ['abbr' => '??', 'label' => $conv->channel->platform_label, 'hex' => '#6B7280', 'bg' => '#F3F4F6'];
            @endphp
            <a href="{{ route('conversations.show', $conv) }}"
               class="flex items-start gap-3 px-3.5 py-3 border-b border-zinc-50 hover:bg-zinc-50 transition-colors relative group"
               style="{{ $isActive ? 'background: #EEF2FF; border-left: 3px solid #6366F1;' : '' }}"
               data-conversation-id="{{ $conv->id }}">

                {{-- Contact avatar --}}
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold"
                         style="background-color: {{ $ac($conv->contact->display_name) }}">
                        {{ mb_strtoupper(mb_substr($conv->contact->display_name, 0, 1)) }}
                    </div>
                    {{-- Platform dot --}}
                    <div class="absolute -bottom-0.5 -right-0.5 w-4.5 h-4.5 rounded-full border-2 border-white flex items-center justify-center"
                         style="background: {{ $cfg['hex'] }}; width:16px; height:16px;">
                        <span style="color:#fff; font-size:7px; font-weight:700; line-height:1;">{{ $cfg['abbr'][0] }}</span>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1">
                        <p class="text-sm font-semibold text-zinc-900 truncate leading-tight">
                            {{ $conv->contact->display_name }}
                        </p>
                        <div class="flex items-center gap-1 shrink-0">
                            @if($conv->is_ai_active)
                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500" title="AI đang bật"></span>
                            @endif
                            <span class="text-xs text-zinc-400 tabular-nums" data-ts="{{ $conv->id }}">
                                {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-semibold"
                              style="background: {{ $cfg['bg'] }}; color: {{ $cfg['hex'] }};">
                            {{ $cfg['abbr'] }}
                        </span>
                        @if($conv->assignedUser)
                        <span class="text-xs text-zinc-400 truncate">{{ $conv->assignedUser->name }}</span>
                        @else
                        <span class="text-xs text-zinc-300">Chưa giao</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-1 mt-1">
                        @php
                        $sdot = ['open' => '#22c55e', 'pending' => '#f59e0b', 'closed' => '#d1d5db'][$conv->status] ?? '#d1d5db';
                        $stxt = ['open' => 'Mở', 'pending' => 'Chờ', 'closed' => 'Đóng'][$conv->status] ?? $conv->status;
                        @endphp
                        <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background: {{ $sdot }}"></span>
                        <span class="text-xs text-zinc-400">{{ $stxt }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 bg-zinc-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-zinc-500">Không có hội thoại</p>
                <p class="text-xs text-zinc-400 mt-1">Thử thay đổi bộ lọc</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($conversations->hasPages())
        <div class="px-3 py-2 border-t border-zinc-100 flex items-center justify-between shrink-0">
            @if($conversations->previousPageUrl())
            <a href="{{ $conversations->previousPageUrl() }}" class="text-xs text-indigo-600 hover:underline">
                ← Trước
            </a>
            @else
            <span></span>
            @endif
            <span class="text-xs text-zinc-400">
                {{ $conversations->currentPage() }} / {{ $conversations->lastPage() }}
            </span>
            @if($conversations->nextPageUrl())
            <a href="{{ $conversations->nextPageUrl() }}" class="text-xs text-indigo-600 hover:underline">
                Sau →
            </a>
            @endif
        </div>
        @endif
    </aside>

    {{-- ════════════════════════════════ --}}
    {{-- PANEL 2 — CHAT AREA             --}}
    {{-- ════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        @isset($conversation)
        @php
            $convPlatform = $conversation->channel->platform;
            $convCfg = $platformCfg[$convPlatform] ?? ['abbr' => '??', 'label' => $conversation->channel->platform_label, 'hex' => '#6B7280', 'bg' => '#F3F4F6'];
        @endphp

        {{-- Chat Header --}}
        <header class="flex items-center justify-between px-5 py-3 bg-white border-b border-zinc-200 shrink-0"
                style="border-top: 2px solid {{ $convCfg['hex'] }};">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Contact avatar --}}
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
                     style="background: {{ $ac($conversation->contact->display_name) }}">
                    {{ mb_strtoupper(mb_substr($conversation->contact->display_name, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-zinc-900 truncate">
                            {{ $conversation->contact->display_name }}
                        </p>
                        {{-- Platform badge --}}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold shrink-0"
                              style="background: {{ $convCfg['bg'] }}; color: {{ $convCfg['hex'] }};">
                            {{ $convCfg['label'] }}
                        </span>
                        {{-- Status --}}
                        @php
                        $sBg = ['open' => '#f0fdf4', 'pending' => '#fefce8', 'closed' => '#f9fafb'][$conversation->status] ?? '#f9fafb';
                        $sTx = ['open' => '#15803d', 'pending' => '#a16207', 'closed' => '#6b7280'][$conversation->status] ?? '#6b7280';
                        $sSt = ['open' => 'Đang mở', 'pending' => 'Chờ xử lý', 'closed' => 'Đã đóng'][$conversation->status] ?? $conversation->status;
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                              style="background: {{ $sBg }}; color: {{ $sTx }};">
                            {{ $sSt }}
                        </span>
                    </div>
                    @if($conversation->contact->phone)
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $conversation->contact->phone }}</p>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-1.5 shrink-0">

                {{-- AI Toggle --}}
                @can('reply-conversations')
                <form action="{{ route('conversations.toggle-ai', $conversation) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                   {{ $conversation->is_ai_active
                                      ? 'bg-violet-100 text-violet-700 hover:bg-violet-200'
                                      : 'bg-zinc-100 text-zinc-500 hover:bg-zinc-200' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        AI {{ $conversation->is_ai_active ? 'ON' : 'OFF' }}
                    </button>
                </form>
                @endcan

                {{-- Assign --}}
                @can('assign-conversations')
                <div class="relative" id="assign-container">
                    <button onclick="document.getElementById('assign-dropdown').classList.toggle('hidden')"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-zinc-100 text-zinc-600 hover:bg-zinc-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $conversation->assignedUser?->name ?? 'Phân công' }}
                    </button>
                    <div id="assign-dropdown"
                         class="hidden absolute right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-zinc-200 py-1.5 z-20 w-52 max-h-48 overflow-y-auto">
                        <form action="{{ route('conversations.assign', $conversation) }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="">
                            <button type="submit" class="w-full text-left px-3.5 py-2 text-xs text-zinc-400 hover:bg-zinc-50 transition-colors">
                                Bỏ phân công
                            </button>
                        </form>
                        @foreach($staffList as $staff)
                        <form action="{{ route('conversations.assign', $conversation) }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $staff->id }}">
                            <button type="submit"
                                    class="w-full text-left px-3.5 py-2 text-xs transition-colors hover:bg-zinc-50
                                           {{ $conversation->assigned_user_id === $staff->id ? 'text-indigo-600 font-semibold' : 'text-zinc-700' }}">
                                {{ $staff->name }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endcan

                {{-- Close / Reopen --}}
                @can('reply-conversations')
                @if($conversation->status !== 'closed')
                <form action="{{ route('conversations.close', $conversation) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                            onclick="return confirm('Đóng hội thoại này?')">
                        Đóng
                    </button>
                </form>
                @else
                <form action="{{ route('conversations.reopen', $conversation) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors">
                        Mở lại
                    </button>
                </form>
                @endif
                @endcan

                {{-- Toggle contact panel --}}
                <button id="toggle-info-panel"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 transition-colors"
                        title="Thông tin liên hệ">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>

            </div>
        </header>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 bg-zinc-50" id="messages-container">
            @foreach($messages as $msg)
            <div class="flex {{ $msg->isFromCustomer() ? 'justify-start' : 'justify-end' }} gap-2.5"
                 data-message-id="{{ $msg->id }}">

                @if($msg->isFromCustomer())
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5"
                     style="background: {{ $ac($conversation->contact->display_name) }}">
                    {{ mb_strtoupper(mb_substr($conversation->contact->display_name, 0, 1)) }}
                </div>
                @endif

                <div class="max-w-xs lg:max-w-sm xl:max-w-md">

                    @if(!$msg->isFromCustomer())
                    <div class="flex items-center justify-end gap-1.5 mb-1">
                        @if($msg->isFromAi())
                        <span class="text-xs font-semibold text-violet-600 bg-violet-50 border border-violet-200 px-2 py-0.5 rounded-full">
                            AI Agent
                        </span>
                        @else
                        <span class="text-xs text-zinc-400 font-medium">Staff</span>
                        @endif
                    </div>
                    @endif

                    @if($msg->isFromCustomer())
                    <div class="bg-white border border-zinc-200 text-zinc-800 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm leading-relaxed shadow-sm">
                        {{ $msg->content }}
                    </div>
                    @elseif($msg->isFromAi())
                    <div class="bg-violet-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed shadow-sm">
                        {{ $msg->content }}
                    </div>
                    @else
                    <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed shadow-sm">
                        {{ $msg->content }}
                    </div>
                    @endif

                    <p class="text-xs text-zinc-400 mt-1 {{ $msg->isFromCustomer() ? 'text-left pl-1' : 'text-right pr-1' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
            @endforeach

            @if($messages->isEmpty())
            <div class="text-center py-12">
                <div class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <p class="text-sm text-zinc-400">Chưa có tin nhắn nào</p>
            </div>
            @endif
        </div>

        {{-- Reply Input --}}
        @if($conversation->status !== 'closed')
        @can('reply-conversations')
        <div class="bg-white border-t border-zinc-200 px-4 py-3 shrink-0">
            <form action="{{ route('messages.store', $conversation) }}" method="POST" id="reply-form">
                @csrf
                <div class="flex items-end gap-2">
                    <div class="flex-1 bg-zinc-50 border border-zinc-200 rounded-xl px-3.5 py-2.5 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-400 transition-all">
                        <textarea name="content" id="reply-input" rows="2"
                                  class="w-full bg-transparent text-sm text-zinc-800 placeholder-zinc-400 resize-none focus:outline-none leading-relaxed"
                                  placeholder="Nhập tin nhắn... (Enter gửi, Shift+Enter xuống dòng)"
                                  required></textarea>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-xs text-zinc-300" style="color: {{ $convCfg['hex'] }}; font-size: 11px; font-weight: 600;">
                                via {{ $convCfg['label'] }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                @if($conversation->is_ai_active)
                                <span class="text-xs text-violet-500">AI đang bật</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-medium text-white transition-all shrink-0 active:scale-95"
                            style="background: {{ $convCfg['hex'] }};">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Gửi
                    </button>
                </div>
            </form>
        </div>
        @endcan
        @else
        <div class="bg-white border-t border-zinc-200 px-4 py-3.5 text-center shrink-0">
            <p class="text-xs text-zinc-400">Hội thoại đã đóng - không thể gửi tin nhắn</p>
        </div>
        @endif

        @else
        {{-- Empty State --}}
        <div class="flex-1 flex items-center justify-center bg-zinc-50">
            <div class="text-center max-w-xs">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #1877F2;">
                        <span class="text-white text-xs font-bold">FB</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #E1306C;">
                        <span class="text-white text-xs font-bold">IG</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #0068FF;">
                        <span class="text-white text-xs font-bold">ZA</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center">
                        <span class="text-white text-xs font-bold">WC</span>
                    </div>
                </div>
                <p class="text-sm font-semibold text-zinc-700 mb-1">Omni-Channel Inbox</p>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    Chọn một hội thoại để xem nội dung.<br>
                    Tin nhắn từ Facebook, Instagram, Zalo OA và WebChat đều hiện ở đây.
                </p>
            </div>
        </div>
        @endisset
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- PANEL 3 — CONTACT INFO          --}}
    {{-- ════════════════════════════════ --}}
    @isset($conversation)
    <aside id="contact-info-panel" class="w-[260px] flex flex-col border-l border-zinc-200 bg-white shrink-0">

        {{-- Panel header --}}
        <div class="px-4 py-3 border-b border-zinc-100 shrink-0">
            <p class="text-xs font-semibold text-zinc-500">Thông tin liên hệ</p>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- Contact section --}}
            <div class="px-4 py-4 border-b border-zinc-100">
                <div class="flex flex-col items-center text-center mb-3">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold mb-2"
                         style="background: {{ $ac($conversation->contact->display_name) }}">
                        {{ mb_strtoupper(mb_substr($conversation->contact->display_name, 0, 1)) }}
                    </div>
                    <p class="text-sm font-semibold text-zinc-900">{{ $conversation->contact->display_name }}</p>
                    @if($conversation->contact->phone)
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $conversation->contact->phone }}</p>
                    @endif
                    @if($conversation->contact->email)
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $conversation->contact->email }}</p>
                    @endif
                </div>

                {{-- Platform IDs --}}
                @if($conversation->contact->platform_ids)
                <div class="space-y-1.5">
                    @foreach($conversation->contact->platform_ids as $platform => $pid)
                    @php $pcfg = $platformCfg[$platform] ?? ['abbr' => '??', 'label' => $platform, 'hex' => '#6B7280', 'bg' => '#F3F4F6']; @endphp
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-xs font-bold shrink-0"
                              style="background: {{ $pcfg['bg'] }}; color: {{ $pcfg['hex'] }};">
                            {{ $pcfg['abbr'] }}
                        </span>
                        <span class="text-xs text-zinc-500 truncate font-mono">{{ $pid }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Conversation details --}}
            <div class="px-4 py-4 border-b border-zinc-100">
                <p class="text-xs font-semibold text-zinc-400 mb-3">Hội thoại này</p>
                <div class="space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Kênh</span>
                        @php $cfg2 = $platformCfg[$conversation->channel->platform] ?? ['abbr' => '??', 'label' => $conversation->channel->platform_label, 'hex' => '#6B7280', 'bg' => '#F3F4F6']; @endphp
                        <span class="text-xs font-medium text-right" style="color: {{ $cfg2['hex'] }};">
                            {{ $conversation->channel->name }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Nền tảng</span>
                        <span class="px-1.5 py-0.5 rounded text-xs font-semibold"
                              style="background: {{ $cfg2['bg'] }}; color: {{ $cfg2['hex'] }};">
                            {{ $cfg2['label'] }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Trạng thái</span>
                        @php
                        $sTx2 = ['open' => '#15803d', 'pending' => '#a16207', 'closed' => '#6b7280'][$conversation->status] ?? '#6b7280';
                        $sSt2 = ['open' => 'Đang mở', 'pending' => 'Chờ xử lý', 'closed' => 'Đã đóng'][$conversation->status] ?? $conversation->status;
                        @endphp
                        <span class="text-xs font-medium" style="color: {{ $sTx2 }}">{{ $sSt2 }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Phụ trách</span>
                        <span class="text-xs text-zinc-600 text-right">
                            {{ $conversation->assignedUser?->name ?? 'Chưa giao' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">AI Agent</span>
                        <span class="text-xs font-medium {{ $conversation->is_ai_active ? 'text-violet-600' : 'text-zinc-400' }}">
                            {{ $conversation->is_ai_active ? 'Đang bật' : 'Tắt' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Tạo lúc</span>
                        <span class="text-xs text-zinc-500 text-right">
                            {{ $conversation->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @if($conversation->last_message_at)
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-zinc-400 shrink-0">Tin cuối</span>
                        <span class="text-xs text-zinc-500 text-right">
                            {{ $conversation->last_message_at->diffForHumans() }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="px-4 py-4">
                <p class="text-xs font-semibold text-zinc-400 mb-3">Thao tác nhanh</p>
                <div class="space-y-2">
                    <a href="{{ route('contacts.index') }}"
                       class="flex items-center gap-2 text-xs text-indigo-600 hover:text-indigo-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Xem profile liên hệ
                    </a>
                </div>
            </div>
        </div>
    </aside>
    @endisset

</div>
@endsection

@push('scripts')
<script>
(function () {
    // Auto-scroll messages to bottom
    const msgContainer = document.getElementById('messages-container');
    if (msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;

    // Enter to send (Shift+Enter = newline)
    const input = document.getElementById('reply-input');
    const form  = document.getElementById('reply-form');
    if (input && form) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim()) form.submit();
            }
        });
    }

    // Toggle contact info panel
    const toggleBtn  = document.getElementById('toggle-info-panel');
    const infoPanel  = document.getElementById('contact-info-panel');
    if (toggleBtn && infoPanel) {
        toggleBtn.addEventListener('click', function () {
            infoPanel.classList.toggle('hidden');
        });
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function (e) {
        const assignDropdown = document.getElementById('assign-dropdown');
        const assignContainer = document.getElementById('assign-container');
        if (assignDropdown && assignContainer && !assignContainer.contains(e.target)) {
            assignDropdown.classList.add('hidden');
        }
        const navDrop = document.getElementById('nav-user-dropdown');
        const navCont = document.getElementById('nav-user-container');
        if (navDrop && navCont && !navCont.contains(e.target)) {
            navDrop.classList.add('hidden');
        }
    });

    // Real-time via Laravel Echo
    // NOTE: app.js is <script type="module"> so it runs AFTER inline scripts.
    // We wait for the 'echo:ready' event dispatched by echo.js.
    function setupEchoSubscription() {
        if (!window.Echo || !window.TENANT_ID) return;

        const currentConversationId = '{{ $conversation->id ?? '' }}';

        window.Echo.private('tenant.' + window.TENANT_ID)
            .listen('.message.received', function (data) {
                const { message, conversation_id, meta } = data;
                const list     = document.getElementById('conversation-list');
                const listItem = list ? list.querySelector('[data-conversation-id="' + conversation_id + '"]') : null;

                if (listItem && list) {
                    // Bump existing conversation card to top + update timestamp
                    list.insertBefore(listItem, list.firstChild);
                    const tsEl = listItem.querySelector('[data-ts]');
                    if (tsEl) tsEl.textContent = 'Vừa xong';
                } else if (list && meta) {
                    // New conversation not in DOM — build and inject a card
                    const card = document.createElement('a');
                    card.href = meta.url;
                    card.className = 'flex items-start gap-3 px-3.5 py-3 border-b border-zinc-50 hover:bg-zinc-50 transition-colors relative group';
                    card.setAttribute('data-conversation-id', conversation_id);
                    card.innerHTML =
                        '<div class="relative shrink-0">' +
                            '<div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background-color:' + meta.avatar_color + '">' +
                                escapeHtml(meta.initial) +
                            '</div>' +
                            '<div class="absolute rounded-full border-2 border-white flex items-center justify-center" style="background:' + meta.platform_hex + ';width:16px;height:16px;bottom:-2px;right:-2px;">' +
                                '<span style="color:#fff;font-size:7px;font-weight:700;">' + escapeHtml(meta.platform_abbr[0]) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="flex-1 min-w-0">' +
                            '<div class="flex items-start justify-between gap-1">' +
                                '<p class="text-sm font-semibold text-zinc-900 truncate leading-tight">' + escapeHtml(meta.contact_name) + '</p>' +
                                '<span class="text-xs text-zinc-400 tabular-nums" data-ts="' + conversation_id + '">Vừa xong</span>' +
                            '</div>' +
                            '<div class="flex items-center gap-1.5 mt-0.5">' +
                                '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold" style="background:' + meta.platform_bg + ';color:' + meta.platform_hex + ';">' + escapeHtml(meta.platform_abbr) + '</span>' +
                                '<span class="text-xs text-zinc-300">Chưa giao</span>' +
                            '</div>' +
                            '<div class="flex items-center gap-1 mt-1">' +
                                '<span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:#22c55e"></span>' +
                                '<span class="text-xs text-zinc-400">Mở</span>' +
                            '</div>' +
                        '</div>';
                    // Remove empty state if present
                    const empty = list.querySelector('.text-center.py-16');
                    if (empty) empty.remove();
                    list.insertAdjacentElement('afterbegin', card);
                }

                // Append message bubble if this conversation is currently open
                if (currentConversationId === conversation_id && msgContainer) {
                    const isCustomer = message.sender_type === 'customer';
                    const isAi       = message.sender_type === 'ai_agent';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex ' + (isCustomer ? 'justify-start' : 'justify-end') + ' gap-2.5';
                    wrapper.dataset.messageId = message.id;

                    let bubbleStyle = '';
                    let bubbleClass = 'text-sm leading-relaxed px-4 py-2.5 shadow-sm';
                    if (isCustomer) {
                        bubbleStyle = 'background:#fff; border:1px solid #e4e4e7; color:#27272a;';
                        bubbleClass += ' rounded-2xl rounded-tl-sm';
                    } else if (isAi) {
                        bubbleStyle = 'background:#7c3aed; color:#fff;';
                        bubbleClass += ' rounded-2xl rounded-tr-sm';
                    } else {
                        bubbleStyle = 'background:#4f46e5; color:#fff;';
                        bubbleClass += ' rounded-2xl rounded-tr-sm';
                    }

                    const badge = !isCustomer
                        ? (isAi
                            ? '<div class="flex justify-end mb-1"><span style="font-size:11px;font-weight:600;color:#7c3aed;background:#f5f3ff;border:1px solid #ede9fe;padding:1px 8px;border-radius:999px;">AI Agent</span></div>'
                            : '<div class="flex justify-end mb-1"><span style="font-size:11px;color:#71717a;font-weight:500;">Staff</span></div>')
                        : '';

                    const time = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

                    wrapper.innerHTML =
                        '<div style="max-width:340px;">' +
                            badge +
                            '<div class="' + bubbleClass + '" style="' + bubbleStyle + '">' + escapeHtml(message.content) + '</div>' +
                            '<p style="font-size:11px;color:#a1a1aa;margin-top:4px;text-align:' + (isCustomer ? 'left' : 'right') + ';">' + time + '</p>' +
                        '</div>';

                    msgContainer.appendChild(wrapper);
                    msgContainer.scrollTop = msgContainer.scrollHeight;
                }

                // Browser notification for other conversations
                if (currentConversationId !== conversation_id) {
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification('Tin nhắn mới — TonCRM', { body: message.content.substring(0, 80) });
                    }
                }
            });
    }

    // Run now if Echo already ready, otherwise wait for the module to load
    if (window.Echo) {
        setupEchoSubscription();
    } else {
        document.addEventListener('echo:ready', setupEchoSubscription, { once: true });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
})();
</script>
@endpush
