@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
<div class="bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Đăng nhập vào hệ thống</h1>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                          @error('email') border-red-400 @enderror"
                   placeholder="admin@demo.com">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Mật khẩu</label>
            <input type="password" name="password" required
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600">
                Ghi nhớ đăng nhập
            </label>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors text-sm">
            Đăng nhập
        </button>
    </form>

    <div class="mt-6 p-3 bg-gray-50 rounded-lg">
        <p class="text-xs text-gray-500 font-medium mb-1">Tài khoản demo:</p>
        <p class="text-xs text-gray-600">Admin: <span class="font-mono">admin@demo.com</span> / <span class="font-mono">password</span></p>
        <p class="text-xs text-gray-600">Staff: <span class="font-mono">staff@demo.com</span> / <span class="font-mono">password</span></p>
    </div>
</div>
@endsection
