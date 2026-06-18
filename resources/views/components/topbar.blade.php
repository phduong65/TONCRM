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
    default => 'dashboard',
};
@endphp

<header class="bg-slate-900 border-b border-slate-800 shrink-0 z-20">
    <div class="flex items-center h-14 px-4 gap-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <span class="text-white font-bold text-base tracking-tight">TonCRM</span>
        </a>

        <nav class="flex items-center gap-1 flex-1">
            @php
            $tabs = [
                ['module' => 'dashboard', 'route' => 'dashboard',            'label' => 'Dashboard'],
                ['module' => 'inbox',     'route' => 'conversations.index',  'label' => 'Inbox',         'perm' => 'view-conversations'],
                ['module' => 'contacts',  'route' => 'contacts.index',       'label' => 'Contacts',      'perm' => 'view-contacts'],
                ['module' => 'channels',  'route' => 'channels.index',       'label' => 'Channels',      'perm' => 'view-channels'],
                ['module' => 'knowledge', 'route' => 'knowledge-bases.index','label' => 'Knowledge Base','perm' => 'view-knowledge-bases'],
                ['module' => 'reports',   'route' => 'reports.index',          'label' => 'Báo cáo',       'perm' => 'view-reports'],
                ['module' => 'settings',  'route' => 'settings.index',         'label' => 'Cài đặt',       'perm' => 'manage-settings'],
            ];
            @endphp
            @foreach($tabs as $tab)
                @if(!isset($tab['perm']) || auth()->user()->can($tab['perm']))
                    <a href="{{ route($tab['route']) }}"
                       class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                              {{ $activeModule === $tab['module']
                                 ? 'bg-indigo-600 text-white'
                                 : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        {{ $tab['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="relative" id="user-menu-container">
            <button onclick="document.getElementById('user-dropdown').classList.toggle('hidden')"
                    class="flex items-center gap-2 text-slate-300 hover:text-white text-sm">
                <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="user-dropdown"
                 class="hidden absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                <div class="px-3 py-2 border-b border-gray-100">
                    <p class="text-xs font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
