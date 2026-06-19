@php
$routeName = request()->route()?->getName() ?? '';
$activeModule = match(true) {
    str_starts_with($routeName, 'conversations') || str_starts_with($routeName, 'messages') => 'inbox',
    str_starts_with($routeName, 'contacts')        => 'contacts',
    str_starts_with($routeName, 'channels')        => 'channels',
    str_starts_with($routeName, 'knowledge-bases') => 'knowledge',
    str_starts_with($routeName, 'reports')         => 'reports',
    str_starts_with($routeName, 'settings')        => 'settings',
    str_starts_with($routeName, 'dashboard')       => 'dashboard',
    str_starts_with($routeName, 'dev')             => 'dev',
    default => 'dashboard',
};
@endphp

<nav class="w-16 h-full bg-slate-950 flex flex-col items-center py-3 shrink-0 z-20 overflow-y-auto">

    {{-- TonCRM Logo --}}
    <a href="{{ route('dashboard') }}"
       class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center mb-5 shrink-0 hover:bg-indigo-500 transition-colors"
       title="TonCRM">
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
    </a>

    {{-- Primary Navigation --}}
    <div class="flex flex-col items-center gap-1 flex-1 w-full px-2">

        {{-- Dashboard --}}
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('dashboard') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'dashboard' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Dashboard">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Dashboard
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>

        {{-- Inbox --}}
        @can('view-conversations')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('conversations.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'inbox' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Inbox">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Inbox
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

        {{-- Contacts --}}
        @can('view-contacts')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('contacts.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'contacts' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Contacts">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Contacts
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

        {{-- Channels --}}
        @can('view-channels')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('channels.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'channels' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Channels">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Channels
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

        {{-- Knowledge Base --}}
        @can('view-knowledge-bases')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('knowledge-bases.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'knowledge' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Knowledge Base">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Knowledge Base
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

        {{-- Reports --}}
        @can('view-reports')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('reports.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'reports' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Báo cáo">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Báo cáo
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

    </div>

    {{-- Bottom Controls --}}
    <div class="flex flex-col items-center gap-1 w-full px-2 mt-2">

        {{-- Settings --}}
        @can('manage-settings')
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('settings.index') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150
                      {{ $activeModule === 'settings' ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:bg-slate-800 hover:text-slate-200' }}"
               title="Cài đặt">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-slate-800 text-slate-100 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Cài đặt
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        @endcan

        {{-- Dev tool (only when APP_DEBUG=true) --}}
        @if(config('app.debug'))
        <div class="relative group w-full flex justify-center">
            <a href="{{ route('dev.chat') }}"
               class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-150 {{ $activeModule === 'dev' ? 'bg-amber-500/20 text-amber-400' : 'text-amber-500 hover:bg-amber-500/10' }}"
               title="Chat Simulator (Dev)">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </a>
            <div class="absolute left-full ml-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1.5 bg-amber-900 text-amber-200 text-xs font-medium rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                Chat Simulator (Dev)
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-amber-900"></div>
            </div>
        </div>
        @endif

        {{-- Divider --}}
        <div class="w-8 border-t border-slate-800 my-1"></div>

        {{-- User Avatar + Dropdown --}}
        <div class="relative group w-full flex justify-center" id="nav-user-container">
            <button onclick="document.getElementById('nav-user-dropdown').classList.toggle('hidden')"
                    class="w-10 h-10 bg-indigo-700 rounded-xl flex items-center justify-center text-white text-sm font-bold hover:bg-indigo-600 transition-colors"
                    title="{{ auth()->user()->name }}">
                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </button>

            <div id="nav-user-dropdown"
                 class="hidden absolute bottom-0 left-full ml-2.5 w-52 bg-white rounded-xl shadow-xl border border-zinc-200 py-1.5 z-50">
                <div class="px-3.5 py-2.5 border-b border-zinc-100">
                    <p class="text-xs font-semibold text-zinc-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-1">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3.5 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('click', function(e) {
        const container = document.getElementById('nav-user-container');
        const dropdown  = document.getElementById('nav-user-dropdown');
        if (container && dropdown && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
