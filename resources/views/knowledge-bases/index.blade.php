@extends('layouts.admin')

@section('title', 'Knowledge Base')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Knowledge Base</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $entries->total() }} nội dung • AI sẽ dùng để trả lời khách hàng</p>
        </div>
        @can('create-knowledge-bases')
        <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            + Thêm nội dung
        </button>
        @endcan
    </div>

    <form action="{{ route('knowledge-bases.index') }}" method="GET" class="mb-4">
        <div class="flex gap-2 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm kiếm tiêu đề, nội dung..."
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">Tìm</button>
            @if(request('search'))
            <a href="{{ route('knowledge-bases.index') }}" class="px-4 py-2 text-gray-500 text-sm rounded-lg hover:bg-gray-100">Xóa</a>
            @endif
        </div>
    </form>

    <div class="space-y-3">
        @forelse($entries as $entry)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        @if($entry->title)
                        <h3 class="font-semibold text-gray-900">{{ $entry->title }}</h3>
                        @endif
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                     {{ $entry->source_type === 'manual' ? 'bg-gray-100 text-gray-600' :
                                        ($entry->source_type === 'url'   ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                            {{ $entry->source_type }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 line-clamp-3">{{ $entry->content }}</p>
                    @if($entry->source_url)
                    <a href="{{ $entry->source_url }}" target="_blank"
                       class="text-xs text-indigo-600 hover:underline mt-1 block truncate">
                        {{ $entry->source_url }}
                    </a>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">{{ $entry->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @can('edit-knowledge-bases')
                    <button onclick="openEditModal({{ json_encode(['id'=>$entry->id,'title'=>$entry->title,'content'=>$entry->content,'source_url'=>$entry->source_url]) }})"
                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Sửa</button>
                    @endcan
                    @can('delete-knowledge-bases')
                    <form action="{{ route('knowledge-bases.destroy', $entry) }}" method="POST"
                          onsubmit="return confirm('Xóa nội dung này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-600 font-medium">Xóa</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <svg class="w-12 h-12 mx-auto text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-sm text-gray-400 mb-1">Chưa có nội dung nào</p>
            <p class="text-xs text-gray-300">Thêm FAQ, chính sách để AI có thể trả lời khách hàng chính xác hơn</p>
        </div>
        @endforelse
    </div>

    @if($entries->hasPages())
    <div class="mt-4">{{ $entries->withQueryString()->links() }}</div>
    @endif
</div>

@can('create-knowledge-bases')
<div id="create-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Thêm nội dung mới</h2>
            <button onclick="document.getElementById('create-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('knowledge-bases.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="source_type" value="manual">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                <input type="text" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="VD: Chính sách đổi trả">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung <span class="text-red-500">*</span></label>
                <textarea name="content" required rows="6"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                          placeholder="Nhập FAQ, chính sách, thông tin sản phẩm...&#10;&#10;AI sẽ sử dụng nội dung này để trả lời khách hàng."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL nguồn <span class="text-gray-400 font-normal">(tuỳ chọn)</span></label>
                <input type="url" name="source_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://...">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Thêm & Tạo Embedding</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('edit-knowledge-bases')
<div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Sửa nội dung</h2>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                <input type="text" name="title" id="edit-title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung <span class="text-red-500">*</span></label>
                <textarea name="content" id="edit-content" required rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL nguồn</label>
                <input type="url" name="source_url" id="edit-source-url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Lưu & Cập nhật Embedding</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function openEditModal(entry) {
    document.getElementById('edit-title').value      = entry.title      || '';
    document.getElementById('edit-content').value    = entry.content    || '';
    document.getElementById('edit-source-url').value = entry.source_url || '';
    document.getElementById('edit-form').action = '/knowledge-bases/' + entry.id;
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>
@endpush
