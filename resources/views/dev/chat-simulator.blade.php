<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Chat Simulator — TonCRM Dev</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .msg-bubble-in  { animation: slideInLeft .2s ease; }
        .msg-bubble-out { animation: slideInRight .2s ease; }
        @keyframes slideInLeft  { from { opacity:0; transform: translateX(-8px); } to { opacity:1; transform: translateX(0); } }
        @keyframes slideInRight { from { opacity:0; transform: translateX(8px); }  to { opacity:1; transform: translateX(0); } }
        .typing-dot { animation: blink 1.2s infinite; }
        .typing-dot:nth-child(2) { animation-delay: .2s; }
        .typing-dot:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0%,100%{opacity:.3;} 50%{opacity:1;} }
    </style>
</head>
<body class="h-full bg-zinc-900 flex items-center justify-center p-6">

    <div class="flex gap-6 w-full max-w-5xl h-full max-h-[760px]">

        {{-- ── Left: Dev Controls ────────────────────────── --}}
        <div class="w-72 shrink-0 flex flex-col gap-4">

            {{-- Dev badge --}}
            <div class="bg-amber-400/20 border border-amber-400/40 rounded-xl px-4 py-3">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                    <span class="text-amber-300 text-xs font-bold tracking-wide">DEV TOOL</span>
                </div>
                <p class="text-amber-200/70 text-xs leading-relaxed">
                    Giả lập tin nhắn từ khách hàng qua WebChat. Dùng để test chức năng inbox và real-time.
                </p>
            </div>

            {{-- Customer profile --}}
            <div class="bg-zinc-800 rounded-xl p-4 border border-zinc-700">
                <p class="text-zinc-400 text-xs font-semibold mb-3">Danh tính khách hàng</p>

                <div class="space-y-2.5">
                    <div>
                        <label class="text-xs text-zinc-500 block mb-1">Tên hiển thị</label>
                        <input type="text" id="customer-name" value="Khách Demo"
                               class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-zinc-500 block mb-1">Customer ID <span class="text-zinc-600">(tự động nếu bỏ trống)</span></label>
                        <input type="text" id="customer-id" placeholder="dev-khach-demo"
                               class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white font-mono focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-zinc-700">
                    <p class="text-xs text-zinc-500 font-semibold mb-2">Mẫu tin nhắn nhanh</p>
                    <div class="flex flex-col gap-1.5">
                        @foreach([
                            'Xin chào, tôi cần hỗ trợ',
                            'Cho tôi xem bảng giá',
                            'Đơn hàng của tôi đâu rồi?',
                            'Tôi muốn đổi trả sản phẩm',
                            'Bạn có ship tỉnh không?',
                        ] as $quick)
                        <button onclick="setQuickMessage('{{ $quick }}')"
                                class="text-left text-xs text-zinc-400 hover:text-white bg-zinc-700/50 hover:bg-zinc-700 rounded-lg px-3 py-2 transition-colors">
                            {{ $quick }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Channel info --}}
            <div class="bg-zinc-800 rounded-xl p-4 border border-zinc-700">
                <p class="text-zinc-400 text-xs font-semibold mb-3">Kết nối</p>
                @if($channel)
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                    <span class="text-xs text-emerald-400 font-medium">Đã kết nối</span>
                </div>
                <div class="space-y-1 text-xs text-zinc-500">
                    <div class="flex justify-between">
                        <span>Kênh</span>
                        <span class="text-zinc-300 font-medium">{{ $channel->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Platform</span>
                        <span class="text-violet-400 font-medium">WebChat</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Channel ID</span>
                        <span class="text-zinc-400 font-mono text-xs">{{ $channel->platform_channel_id }}</span>
                    </div>
                </div>
                @else
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-400"></div>
                    <span class="text-xs text-red-400">Không tìm thấy WebChat channel. Chạy seeder trước!</span>
                </div>
                @endif
            </div>

            {{-- Back to CRM --}}
            <a href="{{ route('conversations.index') }}"
               class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Mở CRM Inbox
            </a>
        </div>

        {{-- ── Right: Chat Widget ────────────────────────── --}}
        <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden border border-zinc-200">

            {{-- Widget header --}}
            <div class="flex items-center gap-3 px-5 py-4 bg-indigo-600 shrink-0">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-white font-semibold text-sm">Demo Company</p>
                    <div class="flex items-center gap-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                        <p class="text-indigo-200 text-xs">Trực tuyến — thường phản hồi trong vài phút</p>
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto px-5 py-5 space-y-3 bg-zinc-50" id="chat-messages">

                {{-- Welcome message --}}
                <div class="flex justify-start msg-bubble-in">
                    <div class="max-w-xs">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">T</span>
                            </div>
                            <span class="text-xs text-zinc-400">TonCRM Support</span>
                        </div>
                        <div class="bg-white border border-zinc-200 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-zinc-700 shadow-sm">
                            Xin chào! Chúng tôi có thể giúp gì cho bạn hôm nay? 👋
                        </div>
                    </div>
                </div>

                {{-- Existing messages --}}
                @foreach($messages as $msg)
                @php $isCustomer = $msg->isFromCustomer(); @endphp
                <div class="flex {{ $isCustomer ? 'justify-end' : 'justify-start' }} {{ $isCustomer ? 'msg-bubble-out' : 'msg-bubble-in' }}">
                    <div class="max-w-xs">
                        @if(!$isCustomer)
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full {{ $msg->isFromAi() ? 'bg-violet-600' : 'bg-indigo-600' }} flex items-center justify-center">
                                <span class="text-white text-xs font-bold">{{ $msg->isFromAi() ? 'AI' : 'S' }}</span>
                            </div>
                            <span class="text-xs text-zinc-400">{{ $msg->isFromAi() ? 'AI Agent' : 'Staff' }}</span>
                        </div>
                        @endif
                        <div class="{{ $isCustomer
                            ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-sm'
                            : 'bg-white border border-zinc-200 text-zinc-700 rounded-2xl rounded-tl-sm shadow-sm' }} px-4 py-2.5 text-sm leading-relaxed">
                            {{ $msg->content }}
                        </div>
                        <p class="text-xs text-zinc-400 mt-1 {{ $isCustomer ? 'text-right' : 'text-left' }}">
                            {{ $msg->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
                @endforeach

                {{-- Typing indicator (hidden) --}}
                <div id="typing-indicator" class="hidden flex justify-start">
                    <div class="bg-white border border-zinc-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm inline-flex items-center gap-1">
                        <div class="typing-dot w-2 h-2 rounded-full bg-zinc-400"></div>
                        <div class="typing-dot w-2 h-2 rounded-full bg-zinc-400"></div>
                        <div class="typing-dot w-2 h-2 rounded-full bg-zinc-400"></div>
                    </div>
                </div>

            </div>

            {{-- Input --}}
            <div class="px-4 py-3 border-t border-zinc-200 bg-white shrink-0">
                @if($channel)
                <form id="chat-form" class="flex items-end gap-2">
                    <div class="flex-1 bg-zinc-50 border border-zinc-200 rounded-xl px-3.5 py-2.5 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-400 transition-all">
                        <textarea id="chat-input" rows="2"
                                  class="w-full bg-transparent text-sm text-zinc-800 placeholder-zinc-400 resize-none focus:outline-none"
                                  placeholder="Nhập tin nhắn... (Enter gửi)"
                                  required></textarea>
                    </div>
                    <button type="submit"
                            class="w-10 h-10 bg-indigo-600 hover:bg-indigo-700 rounded-xl flex items-center justify-center text-white transition-all active:scale-95 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
                @else
                <p class="text-sm text-center text-zinc-400 py-2">Chưa có WebChat channel. Chạy <code class="bg-zinc-100 px-1 rounded">php artisan db:seed</code> trước.</p>
                @endif
            </div>

        </div>
    </div>

    <script>
    (function () {
        const msgContainer = document.getElementById('chat-messages');
        const form         = document.getElementById('chat-form');
        const input        = document.getElementById('chat-input');
        const typingEl     = document.getElementById('typing-indicator');

        // scroll to bottom
        if (msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;

        // quick messages
        window.setQuickMessage = function(text) {
            if (input) { input.value = text; input.focus(); }
        };

        // Enter to send
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (this.value.trim()) form.dispatchEvent(new Event('submit'));
                }
            });
        }

        // Send message
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const content = input.value.trim();
                if (!content) return;

                const customerName = document.getElementById('customer-name')?.value || 'Khách Demo';
                const customerId   = document.getElementById('customer-id')?.value || '';

                // Optimistic UI: show customer's message immediately
                appendMessage('customer', content, 'Vừa xong');
                input.value = '';
                msgContainer.scrollTop = msgContainer.scrollHeight;

                try {
                    const resp = await fetch('{{ route('dev.chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ content, customer_name: customerName, customer_id: customerId }),
                    });

                    const data = await resp.json();

                    if (data.ok && data.conversation_id && window.Echo) {
                        // Subscribe to tenant channel for AI/Staff replies
                        subscribeToReplies(data.conversation_id);
                    }
                } catch (err) {
                    console.error('Send failed:', err);
                }
            });
        }

        // Track subscribed conversations to avoid duplicate listeners
        const subscribedConvs = new Set();

        function subscribeToReplies(conversationId) {
            if (subscribedConvs.has(conversationId)) return;
            subscribedConvs.add(conversationId);

            if (!window.Echo || !window.TENANT_ID) return;

            window.Echo.private('tenant.' + window.TENANT_ID)
                .listen('.message.received', function(data) {
                    if (data.conversation_id !== conversationId) return;
                    const msg = data.message;
                    if (msg.sender_type === 'customer') return; // already shown optimistically

                    hiddeTyping();
                    const isAi   = msg.sender_type === 'ai_agent';
                    const label  = isAi ? 'AI Agent' : 'Staff';
                    const letter = isAi ? 'AI' : 'S';
                    const bg     = isAi ? '#7c3aed' : '#4f46e5';

                    appendMessage('agent', msg.content, new Date().toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'}), label, letter, bg);
                    msgContainer.scrollTop = msgContainer.scrollHeight;
                });

            // Show typing indicator after 1s (simulates response latency)
            setTimeout(showTyping, 1000);
        }

        function showTyping() {
            if (typingEl) {
                typingEl.classList.remove('hidden');
                typingEl.classList.add('flex');
                msgContainer.scrollTop = msgContainer.scrollHeight;
            }
        }

        function hiddeTyping() {
            if (typingEl) {
                typingEl.classList.add('hidden');
                typingEl.classList.remove('flex');
            }
        }

        function appendMessage(role, content, time, label, letter, bgColor) {
            const isCustomer = role === 'customer';
            const div = document.createElement('div');
            div.className = 'flex ' + (isCustomer ? 'justify-end msg-bubble-out' : 'justify-start msg-bubble-in');

            if (isCustomer) {
                div.innerHTML = `
                    <div class="max-w-xs">
                        <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed">${escapeHtml(content)}</div>
                        <p class="text-xs text-zinc-400 mt-1 text-right">${time}</p>
                    </div>`;
            } else {
                div.innerHTML = `
                    <div class="max-w-xs">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center" style="background:${bgColor}">
                                <span class="text-white font-bold" style="font-size:9px;">${escapeHtml(letter)}</span>
                            </div>
                            <span class="text-xs text-zinc-400">${escapeHtml(label)}</span>
                        </div>
                        <div class="bg-white border border-zinc-200 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-zinc-700 leading-relaxed shadow-sm">${escapeHtml(content)}</div>
                        <p class="text-xs text-zinc-400 mt-1 text-left">${time}</p>
                    </div>`;
            }

            // Insert before typing indicator
            if (typingEl) {
                msgContainer.insertBefore(div, typingEl);
            } else {
                msgContainer.appendChild(div);
            }
        }

        function escapeHtml(t) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(t));
            return d.innerHTML;
        }
    })();
    </script>

    <script>
        window.TENANT_ID      = '{{ auth()->user()->tenant_id }}';
        window.REVERB_HOST    = '{{ config('reverb.apps.apps.0.options.host', 'localhost') }}';
        window.REVERB_PORT    = {{ config('reverb.apps.apps.0.options.port', 8080) }};
        window.REVERB_APP_KEY = '{{ config('reverb.apps.apps.0.key', '') }}';
    </script>

</body>
</html>
