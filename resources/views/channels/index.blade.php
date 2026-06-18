@extends('layouts.admin')

@section('title', 'Channels')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Kênh kết nối</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $channels->count() }} kênh</p>
        </div>
        @can('create-channels')
        <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            + Thêm kênh
        </button>
        @endcan
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($channels as $channel)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs px-2 py-1 rounded-md font-medium {{ $channel->platform_color }}">
                        {{ $channel->platform_label }}
                    </span>
                    @if($channel->is_active)
                    <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Hoạt động
                    </span>
                    @else
                    <span class="text-xs text-gray-400">Tắt</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @can('edit-channels')
                    <button onclick="openEditModal({{ json_encode(['id'=>$channel->id,'name'=>$channel->name,'access_token'=>$channel->access_token,'webhook_secret'=>$channel->webhook_secret,'is_active'=>$channel->is_active]) }})"
                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Sửa</button>
                    @endcan
                    @can('delete-channels')
                    <form action="{{ route('channels.destroy', $channel) }}" method="POST"
                          onsubmit="return confirm('Xóa kênh? Mọi hội thoại liên quan sẽ bị xóa.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-600 font-medium">Xóa</button>
                    </form>
                    @endcan
                </div>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">{{ $channel->name }}</h3>
            <p class="text-xs text-gray-400 font-mono truncate mb-3">ID: {{ $channel->platform_channel_id }}</p>
            <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                <span class="text-xs text-gray-400">{{ $channel->conversations_count }} hội thoại</span>
                <span class="text-xs font-mono text-indigo-600">/api/webhooks/{{ $channel->platform }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm">Chưa có kênh nào. Thêm kênh để bắt đầu nhận tin nhắn.</p>
        </div>
        @endforelse
    </div>
</div>

@can('create-channels')
<div id="create-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Thêm kênh mới</h2>
            <button onclick="document.getElementById('create-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('channels.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform <span class="text-red-500">*</span></label>
                <select name="platform" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Chọn platform</option>
                    <option value="facebook">Facebook</option>
                    <option value="zalo">Zalo OA</option>
                    <option value="tiktok">TikTok</option>
                    <option value="webchat">WebChat</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên kênh <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="VD: Facebook Page chính">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Page / Channel ID <span class="text-red-500">*</span></label>
                <input type="text" name="platform_channel_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Access Token <span class="text-red-500">*</span></label>
                <textarea name="access_token" required rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                <input type="text" name="webhook_secret" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
            </div>
            <div class="bg-blue-50 rounded-lg p-3">
                <p class="text-xs font-medium text-blue-700 mb-1">Webhook URL:</p>
                <code class="text-xs text-blue-600">{{ url('/api/webhooks/{platform}') }}</code>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Thêm kênh</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('edit-channels')
<div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Sửa kênh</h2>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên kênh</label>
                <input type="text" name="name" id="edit-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                <textarea name="access_token" id="edit-access-token" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                <input type="text" name="webhook_secret" id="edit-webhook-secret" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="edit-is-active" value="1" class="rounded border-gray-300 text-indigo-600">
                <label for="edit-is-active" class="text-sm text-gray-700">Kênh đang hoạt động</label>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Lưu</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function openEditModal(channel) {
    document.getElementById('edit-name').value           = channel.name || '';
    document.getElementById('edit-access-token').value  = channel.access_token || '';
    document.getElementById('edit-webhook-secret').value = channel.webhook_secret || '';
    document.getElementById('edit-is-active').checked   = !!channel.is_active;
    document.getElementById('edit-form').action = '/channels/' + channel.id;
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>
@endpush
