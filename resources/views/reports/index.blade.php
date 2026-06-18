@extends('layouts.admin')

@section('title', 'Báo cáo')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Báo cáo</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}
            </p>
        </div>
        @can('export-reports')
        <a href="{{ route('reports.export', request()->only('from', 'to')) }}"
           class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            Xuất Excel
        </a>
        @endcan
    </div>

    {{-- Date filter --}}
    <form action="{{ route('reports.index') }}" method="GET" class="mb-6 flex items-center gap-3">
        <label class="text-sm text-gray-600 font-medium">Từ</label>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <label class="text-sm text-gray-600 font-medium">Đến</label>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            Lọc
        </button>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
    </form>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tổng hội thoại</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($byStatus['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Đang mở</p>
            <p class="text-3xl font-bold text-green-600">{{ number_format($byStatus['open']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Chờ xử lý</p>
            <p class="text-3xl font-bold text-yellow-600">{{ number_format($byStatus['pending']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tin nhắn hôm nay</p>
            <p class="text-3xl font-bold text-indigo-600">{{ number_format($messagesToday) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- By channel --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Hội thoại theo kênh</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Kênh</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Platform</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Hội thoại</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($byChannel as $channel)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $channel->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($channel->platform === 'facebook') bg-blue-100 text-blue-700
                                @elseif($channel->platform === 'zalo') bg-cyan-100 text-cyan-700
                                @elseif($channel->platform === 'tiktok') bg-pink-100 text-pink-700
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ ucfirst($channel->platform) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $channel->conv_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-8 text-gray-400 text-sm">Chưa có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- By day --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Hội thoại theo ngày</h2>
            </div>
            <div class="overflow-y-auto max-h-80">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Ngày</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Hội thoại mới</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php $maxCount = $byDay->max('count') ?: 1; @endphp
                        @foreach($byDay->reverse() as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-700 font-medium">{{ $row['date'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">{{ $row['count'] }}</td>
                            <td class="px-4 py-2.5 w-32">
                                <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-2 rounded-full"
                                         style="width: {{ $row['count'] / $maxCount * 100 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
