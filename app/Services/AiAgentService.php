<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    public function __construct(private PineconeService $pinecone) {}

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
        $context   = $embedding
            ? $this->pinecone->search($conversation->tenant_id, $embedding)
            : '';

        // Fallback: MySQL FULLTEXT search khi Pinecone không có kết quả
        if (empty($context)) {
            $context = $this->fullTextSearch($conversation->tenant_id, $lastMessage);
        }

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

        try {
            $response = Http::withToken($key)
                ->timeout(10)
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => mb_substr($text, 0, 8000),
                ])->json();

            return $response['data'][0]['embedding'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('AiAgentService: embedding failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fullTextSearch(string $tenantId, string $query): string
    {
        try {
            return KnowledgeBase::where('tenant_id', $tenantId)
                ->whereFullText('content', $query)
                ->limit(3)
                ->pluck('content')
                ->implode("\n---\n");
        } catch (\Throwable $e) {
            Log::warning('AiAgentService: fulltext search failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    private function callLlm(string $systemPrompt, array $messages): string
    {
        $key = config('services.openai.key');
        if (!$key) return '';

        try {
            $response = Http::withToken($key)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'      => 'gpt-4o-mini',
                    'max_tokens' => 500,
                    'messages'   => array_merge(
                        [['role' => 'system', 'content' => $systemPrompt]],
                        $messages
                    ),
                ])->json();

            return $response['choices'][0]['message']['content'] ?? '';
        } catch (\Throwable $e) {
            Log::error('AiAgentService: LLM call failed', ['error' => $e->getMessage()]);
            return '';
        }
    }
}
