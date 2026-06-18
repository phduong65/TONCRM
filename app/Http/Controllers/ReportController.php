<?php

namespace App\Http\Controllers;

use App\Exports\ConversationExport;
use App\Models\Channel;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->filled('from') ? $request->date('from') : now()->subDays(29)->startOfDay();
        $to   = $request->filled('to')   ? $request->date('to')->endOfDay() : now()->endOfDay();

        $baseQuery = Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to]);

        $byStatus = [
            'open'    => (clone $baseQuery)->where('status', 'open')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'closed'  => (clone $baseQuery)->where('status', 'closed')->count(),
        ];
        $byStatus['total'] = array_sum($byStatus);

        $messagesToday = Message::whereHas(
            'conversation',
            fn($q) => $q->where('tenant_id', $tenantId)
        )->whereDate('created_at', today())->count();

        $byChannel = Channel::where('tenant_id', $tenantId)
            ->withCount(['conversations as conv_count' => fn($q) =>
                $q->whereBetween('created_at', [$from, $to])
            ])
            ->orderByDesc('conv_count')
            ->get();

        $byDay = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $day = $cursor->copy();
            $byDay->push([
                'date'  => $day->format('d/m'),
                'count' => (clone $baseQuery)
                    ->whereDate('created_at', $day->toDateString())
                    ->count(),
            ]);
            $cursor->addDay();
        }

        return view('reports.index', compact('byStatus', 'messagesToday', 'byChannel', 'byDay', 'from', 'to'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $from = $request->filled('from') ? $request->date('from') : now()->subDays(29)->startOfDay();
        $to   = $request->filled('to')   ? $request->date('to')->endOfDay() : now()->endOfDay();

        $filename = 'conversations_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx';

        return Excel::download(new ConversationExport($tenantId, $from, $to), $filename);
    }
}
