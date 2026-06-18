<?php

namespace App\Jobs;

use App\Models\KnowledgeBase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly KnowledgeBase $entry) {}

    public function handle(): void
    {
        $key = config('services.openai.key');
        if (!$key) return;

        $text = $this->entry->title
            ? "{$this->entry->title}\n\n{$this->entry->content}"
            : $this->entry->content;

        $response = Http::withToken($key)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => mb_substr($text, 0, 8000),
            ])->json();

        $embedding = $response['data'][0]['embedding'] ?? null;
        if (!$embedding) return;

        $vectorStr = '[' . implode(',', $embedding) . ']';

        DB::statement(
            'UPDATE knowledge_bases SET embedding = ?::vector WHERE id = ?',
            [$vectorStr, $this->entry->id]
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateEmbeddingJob failed', [
            'entry_id' => $this->entry->id,
            'error'    => $exception->getMessage(),
        ]);
    }
}
