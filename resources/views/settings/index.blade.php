@extends('layouts.admin')

@section('title', 'Cài đặt')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Cài đặt</h1>
        <p class="text-sm text-gray-500 mt-0.5">Quản lý người dùng và cài đặt hệ thống</p>
    </div>

    {{-- Tabs --}}
    @php $tab = request('tab', 'users'); @endphp
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <a href="{{ route('settings.index', ['tab' => 'users']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                  {{ $tab === 'users' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Người dùng
        </a>
        @can('manage-settings')
        <a href="{{ route('settings.index', ['tab' => 'general']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                  {{ $tab === 'general' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Cài đặt chung
        </a>
        @endcan
    </div>

    @if($tab === 'users')
    {{-- Users tab --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $users->count() }} người dùng trong tenant</p>
        @can('manage-users')
        <button onclick="document.getElementById('create-user-modal').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            + Thêm người dùng
        </button>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Tên</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Email</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Vai trò</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Trạng thái</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Ngày tạo</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs shrink-0">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-900">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                <span class="text-xs text-gray-400">(bạn)</span>
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach($user->roles as $role)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($role->name === 'admin') bg-red-100 text-red-700
                            @elseif($role->name === 'manager') bg-purple-100 text-purple-700
                            @elseif($role->name === 'staff') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $role->name }}
                        </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        @if($user->is_active)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Hoạt động</span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Vô hiệu</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @can('manage-users')
                        @if($user->id !== auth()->id())
                        <div class="flex items-center gap-2 justify-end">
                            <button onclick="openEditUserModal({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->roles->first()?->name, 'is_active' => $user->is_active]) }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Sửa</button>
                            <form action="{{ route('settings.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Xóa người dùng {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-600 font-medium">Xóa</button>
                            </form>
                        </div>
                        @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Chưa có người dùng nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @elseif($tab === 'general')
    {{-- General settings tab --}}
    @can('manage-settings')
    <div class="max-w-lg">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Thông tin Tenant</h2>
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên doanh nghiệp</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gói dịch vụ</label>
                    <input type="text" value="{{ ucfirst($tenant->plan) }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Lưu cài đặt
                </button>
            </form>
        </div>
    </div>
    @endcan
    @endif
</div>

{{-- Create User Modal --}}
@can('manage-users')
<div id="create-user-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Thêm người dùng mới</h2>
            <button onclick="document.getElementById('create-user-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('settings.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Nguyễn Văn A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="nhanvien@company.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Tối thiểu 8 ký tự">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò <span class="text-red-500">*</span></label>
                <select name="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Chọn vai trò --</option>
                    @foreach($roles as $role)
                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('create-user-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Tạo</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit User Modal --}}
<div id="edit-user-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Sửa người dùng</h2>
            <button onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-user-form" action="" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit-user-name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="edit-user-email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                <select name="role" id="edit-user-role"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($roles as $role)
                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="edit-user-active" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="edit-user-active" class="text-sm font-medium text-gray-700">Tài khoản đang hoạt động</label>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('edit-user-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Lưu</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function openEditUserModal(user) {
    document.getElementById('edit-user-name').value  = user.name  || '';
    document.getElementById('edit-user-email').value = user.email || '';
    document.getElementById('edit-user-role').value  = user.role  || '';
    document.getElementById('edit-user-active').checked = user.is_active;
    document.getElementById('edit-user-form').action = '/settings/users/' + user.id;
    document.getElementById('edit-user-modal').classList.remove('hidden');
}
</script>
@endpush
