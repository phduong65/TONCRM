@extends('layouts.admin')
@section('title', 'Chat Simulator')

@section('content')
@php
    $platformCfg = [
        'facebook'  => ['hex' => '#1877F2', 'bg' => '#EBF3FF', 'abbr' => 'FB'],
        'instagram' => ['hex' => '#E1306C', 'bg' => '#FEE9F0', 'abbr' => 'IG'],
        'zalo'      => ['hex' => '#0068FF', 'bg' => '#E5EEFF', 'abbr' => 'ZA'],
        'webchat'   => ['hex' => '#6366F1', 'bg' => '#EDEEFF', 'abbr' => 'WC'],
    ];
@endphp

<div class="h-full flex flex-col overflow-hidden">

    {{-- Header --}}
    <div class="bg-white border-b border-zinc-200 px-6 py-3 shrink-0 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-semibold text-zinc-900">Chat Simulator</h1>
                <p class="text-xs text-zinc-400">Gửi tin nhắn giả lập để kiểm tra real-time</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">DEV ONLY</span>
        </div>
        <a href="{{ route('conversations.index') }}" target="_blank"
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Mở Inbox
        </a>
    </div>

    <div class="flex-1 flex overflow-hidden">

        {{-- ── PANEL TRÁI: Form gửi tin nhắn ────────────────── --}}
        <div class="w-[360px] shrink-0 flex flex-col border-r border-zinc-200 bg-white overflow-y-auto">

            <div class="p-5 space-y-4">

                {{-- Channel picker --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 mb-1.5">Kênh</label>
                    @if($channels->isEmpty())
                    <div class="px-3 py-2.5 rounded-lg border border-zinc-200 bg-zinc-50 text-xs text-zinc-400">
                        Chưa có kênh nào. <a href="{{ route('channels.index') }}" class="text-indigo-600 underline">Thêm kênh</a>
                    </div>
                    @else
                    <div class="space-y-1.5">
                        @foreach($channels as $ch)
                        @php $cfg = $platformCfg[$ch->platform] ?? ['hex' => '#6B7280', 'bg' => '#F3F4F6', 'abbr' => '??']; @endphp
                        <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border border-zinc-200 cursor-pointer hover:bg-zinc-50 transition-colors has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50">
                            <input type="radio" name="channel_id" value="{{ $ch->id }}"
                                   class="channel-radio accent-indigo-600"
                                   data-platform="{{ $ch->platform }}"
                                   {{ $loop->first ? 'checked' : '' }}>
                            <span class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold shrink-0"
                                  style="background:{{ $cfg['bg'] }}; color:{{ $cfg['hex'] }};">
                                {{ $cfg['abbr'] }}
                            </span>
                            <span class="text-sm font-medium text-zinc-800 truncate">{{ $ch->name }}</span>
                            <span class="ml-auto text-xs text-zinc-400">{{ $ch->platform_label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Sender name --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 mb-1.5">Tên khách hàng</label>
                    <input type="text" id="sender-name" value="Khách Demo"
                           class="w-full px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400">
                    <div class="flex gap-1.5 mt-1.5">
                        @foreach(['Khách Demo', 'Nguyễn Văn A', 'Trần Thị B'] as $name)
                        <button type="button" onclick="document.getElementById('sender-name').value='{{ $name }}'"
                                class="px-2 py-0.5 rounded text-xs bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-colors">
                            {{ $name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Message input --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 mb-1.5">Tin nhắn</label>
                    <textarea id="message-content" rows="3"
                              placeholder="Nhập nội dung tin nhắn... (Enter gửi)"
                              class="w-full px-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 resize-none"></textarea>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach(['Xin chào!', 'Cho hỏi về sản phẩm?', 'Giá bao nhiêu?', 'Cảm ơn bạn nhé!'] as $q)
                        <button type="button"
                                onclick="document.getElementById('message-content').value='{{ $q }}'"
                                class="px-2 py-0.5 rounded text-xs bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-colors">
                            {{ $q }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Send button --}}
                <button id="send-btn"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span id="send-label">Gửi tin nhắn</span>
                </button>

            </div>

            {{-- Sent history --}}
            <div class="border-t border-zinc-100 px-5 py-4 flex-1">
                <p class="text-xs font-semibold text-zinc-400 mb-3">Đã gửi trong phiên này</p>
                <div id="sent-history" class="space-y-2">
                    <p class="text-xs text-zinc-300">Chưa có tin nhắn nào.</p>
                </div>
            </div>
        </div>

        {{-- ── PANEL PHẢI: Real-time log ──────────────────────── --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-zinc-50">

            {{-- Log header --}}
            <div class="px-5 py-3 bg-white border-b border-zinc-200 shrink-0 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div id="echo-status-dot" class="w-2 h-2 rounded-full bg-zinc-300"></div>
                    <span class="text-xs font-semibold text-zinc-600">Echo / Pusher Events</span>
                    <span id="echo-status-text" class="text-xs text-zinc-400">Đang kết nối...</span>
                </div>
                <button onclick="document.getElementById('event-log').innerHTML='<p class=\'text-xs text-zinc-300 text-center mt-8\'>Log đã xoá.</p>'"
                        class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                    Xoá log
                </button>
            </div>

            {{-- Event log --}}
            <div id="event-log" class="flex-1 overflow-y-auto p-5 space-y-2 font-mono text-xs">
                <p class="text-zinc-300 text-center mt-8">Sự kiện từ Pusher sẽ xuất hiện ở đây...</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Echo status indicator ────────────────────────────────────
    function setStatus(state) {
        const dot  = document.getElementById('echo-status-dot');
        const text = document.getElementById('echo-status-text');
        const map  = {
            connecting: ['bg-amber-400', 'Đang kết nối...'],
            connected:  ['bg-emerald-500', 'Đã kết nối Pusher'],
            failed:     ['bg-red-500', 'Kết nối thất bại'],
        };
        const [cls, label] = map[state] ?? ['bg-zinc-300', state];
        dot.className  = 'w-2 h-2 rounded-full ' + cls;
        text.textContent = label;
    }

    // ── Echo subscription ────────────────────────────────────────
    function setupEcho() {
        if (!window.Echo || !window.TENANT_ID) {
            setStatus('failed');
            return;
        }

        setStatus('connecting');

        const channel = window.Echo.private('tenant.' + window.TENANT_ID);

        channel.subscribed(() => setStatus('connected'));
        channel.error(() => setStatus('failed'));

        channel.listen('.message.received', function (data) {
            appendEvent(data);
        });
    }

    if (window.Echo) {
        setupEcho();
    } else {
        document.addEventListener('echo:ready', setupEcho, { once: true });
    }

    // ── Append event to log ──────────────────────────────────────
    function appendEvent(data) {
        const log = document.getElementById('event-log');

        // Clear placeholder
        const placeholder = log.querySelector('p');
        if (placeholder) placeholder.remove();

        const { message, meta } = data;
        const time = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        const senderColors = {
            customer: { bg: '#f0f9ff', border: '#bae6fd', label: '#0369a1' },
            staff:    { bg: '#f0fdf4', border: '#bbf7d0', label: '#166534' },
            ai_agent: { bg: '#faf5ff', border: '#e9d5ff', label: '#6b21a8' },
        };
        const sc = senderColors[message?.sender_type] ?? { bg: '#fafafa', border: '#e4e4e7', label: '#71717a' };

        const platformAbbr = (meta?.platform_abbr ?? '??').toUpperCase();
        const platformHex  = meta?.platform_hex ?? '#6B7280';
        const platformBg   = meta?.platform_bg  ?? '#F3F4F6';

        const card = document.createElement('div');
        card.style.cssText = `background:${sc.bg}; border:1px solid ${sc.border}; border-radius:8px; padding:10px 12px;`;
        card.innerHTML =
            '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">' +
                '<div style="display:flex; align-items:center; gap:6px;">' +
                    '<span style="background:' + platformBg + '; color:' + platformHex + '; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:700;">' + esc(platformAbbr) + '</span>' +
                    '<span style="font-weight:600; color:' + sc.label + ';">' + esc(meta?.contact_name ?? message?.sender_type) + '</span>' +
                    '<span style="color:#a1a1aa; font-size:10px;">(' + esc(message?.sender_type ?? '') + ')</span>' +
                '</div>' +
                '<span style="color:#a1a1aa;">' + time + '</span>' +
            '</div>' +
            '<div style="color:#3f3f46; word-break:break-word;">' + esc(message?.content ?? '') + '</div>' +
            (data.conversation_id
                ? '<div style="margin-top:6px;"><a href="/conversations/' + esc(data.conversation_id) + '" target="_blank" ' +
                  'style="color:#6366f1; font-size:10px; text-decoration:underline;">→ Xem hội thoại</a></div>'
                : '') +
            '<div style="margin-top:4px; color:#a1a1aa; font-size:10px;">conv_id: ' + esc(data.conversation_id ?? '-') + '</div>';

        log.insertAdjacentElement('afterbegin', card);
    }

    // ── Send message ─────────────────────────────────────────────
    const sendBtn   = document.getElementById('send-btn');
    const sendLabel = document.getElementById('send-label');
    const history   = document.getElementById('sent-history');

    sendBtn.addEventListener('click', sendMessage);
    document.getElementById('message-content').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    async function sendMessage() {
        const channelRadio = document.querySelector('.channel-radio:checked');
        const senderName   = document.getElementById('sender-name').value.trim();
        const content      = document.getElementById('message-content').value.trim();

        if (!channelRadio) { flash('Chọn kênh trước!', 'error'); return; }
        if (!senderName)   { flash('Nhập tên khách hàng!', 'error'); return; }
        if (!content)      { flash('Nhập nội dung tin nhắn!', 'error'); return; }

        sendBtn.disabled  = true;
        sendLabel.textContent = 'Đang gửi...';

        try {
            const res = await fetch('{{ route('dev.chat.send') }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ channel_id: channelRadio.value, sender_name: senderName, content }),
            });

            const json = await res.json();

            if (json.ok) {
                document.getElementById('message-content').value = '';
                appendSentHistory({ senderName, content, json });
                flash('Đã gửi! Xem log bên phải.', 'ok');
            } else {
                flash('Lỗi: ' + (json.message ?? 'Không rõ'), 'error');
            }
        } catch (err) {
            flash('Lỗi kết nối: ' + err.message, 'error');
        } finally {
            sendBtn.disabled  = false;
            sendLabel.textContent = 'Gửi tin nhắn';
        }
    }

    function appendSentHistory({ senderName, content, json }) {
        const placeholder = history.querySelector('p');
        if (placeholder) placeholder.remove();

        const item = document.createElement('div');
        item.className = 'px-3 py-2 rounded-lg bg-zinc-50 border border-zinc-200';
        item.innerHTML =
            '<div style="display:flex; justify-content:space-between; margin-bottom:2px;">' +
                '<span style="font-size:11px; font-weight:600; color:#3f3f46;">' + esc(senderName) + '</span>' +
                '<span style="font-size:11px; color:#a1a1aa;">' + json.sent_at + '</span>' +
            '</div>' +
            '<p style="font-size:12px; color:#71717a; margin:0;">' + esc(content.substring(0, 80)) + '</p>' +
            (json.conversation_url
                ? '<a href="' + json.conversation_url + '" target="_blank" style="font-size:10px; color:#6366f1; text-decoration:underline;">→ Hội thoại</a>'
                : '');

        history.insertAdjacentElement('afterbegin', item);
    }

    function flash(msg, type) {
        const el = document.createElement('div');
        el.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:9999; padding:10px 16px; border-radius:8px; font-size:13px; font-weight:500; box-shadow:0 4px 12px rgba(0,0,0,.12);'
            + (type === 'ok' ? 'background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;' : 'background:#fef2f2; color:#991b1b; border:1px solid #fecaca;');
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 2500);
    }

    function esc(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str ?? '')));
        return d.innerHTML;
    }
})();
</script>
@endpush
