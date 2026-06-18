<?php

namespace App\Exports;

use App\Models\Conversation;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConversationExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $tenantId,
        private readonly Carbon $from,
        private readonly Carbon $to,
    ) {}

    public function query()
    {
        return Conversation::with(['contact', 'channel'])
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['ID', 'Liên hệ', 'Kênh', 'Platform', 'Trạng thái', 'AI Active', 'Ngày tạo', 'Tin nhắn cuối'];
    }

    public function map($conversation): array
    {
        return [
            $conversation->id,
            $conversation->contact?->name ?? 'Không tên',
            $conversation->channel?->name ?? '—',
            $conversation->channel?->platform ?? '—',
            $conversation->status,
            $conversation->is_ai_active ? 'Bật' : 'Tắt',
            $conversation->created_at->format('d/m/Y H:i'),
            $conversation->last_message_at?->format('d/m/Y H:i') ?? '—',
        ];
    }
}
