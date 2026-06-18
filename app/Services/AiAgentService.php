<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiAgentService
{
    public function generateReply(Conversation $conversation): ?string
    {
        $history = $conversation->messages()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->reverse();

        $lastMessage = $history->last()?->content;
        if (!$lastMessage) return null;

        $embedding = $this->getEmbedding($lastMessage);
        $context   = $embedding ? $this->searchKnowledgeBase($conversation->tenant_id, $embedding) : '';

        $systemPrompt = "Bạn là trợ lý CSKH của doanh nghiệp. Trả lời ngắn gọn, thân thiện bằng tiếng Việt.";
        if ($context) {
            $systemPrompt .= "\n\nKiến thức tham khảo:\n" . $context;
        }

        $messages = $history->map(fn($m) => [
            'role'    => $m->sender_type === 'customer' ? 'user' : 'assistant',
            'content' => $m->content,
        ])->values()->toArray();

        return $this->callLlm($systemPrompt, $messages);
    }

    private function getEmbedding(string $text): ?array
    {
        $key = config('services.openai.key');
        if (!$key) return null;

        $response = Http::withToken($key)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ])->json();

        return $response['data'][0]['embedding'] ?? null;
    }

    private function searchKnowledgeBase(string $tenantId, array $embedding): string
    {
        $vectorStr = '[' . implode(',', $embedding) . ']';

        $results = DB::select("
            SELECT content, 1 - (embedding <=> ?) AS similarity
            FROM knowledge_bases
            WHERE tenant_id = ?
              AND embedding IS NOT NULL
            ORDER BY embedding <=> ?
            LIMIT 3
        ", [$vectorStr, $tenantId, $vectorStr]);

        return collect($results)
            ->filter(fn($r) => $r->similarity > 0.7)
            ->pluck('content')
            ->implode("\n---\n");
    }

    private function callLlm(string $systemPrompt, array $messages): string
    {
        $key = config('services.openai.key');
        if (!$key) return '';

        $response = Http::withToken($key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'max_tokens' => 500,
                'messages'   => array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $messages
                ),
            ])->json();

        return $response['choices'][0]['message']['content'] ?? '';
    }
}
