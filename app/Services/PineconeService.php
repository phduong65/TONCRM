<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PineconeService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.pinecone.host'), '/');
        $this->apiKey  = config('services.pinecone.key');
    }

    public function upsert(string $id, array $embedding, string $tenantId, string $content): void
    {
        if (!$this->apiKey || !$this->baseUrl) return;

        Http::withHeaders(['Api-Key' => $this->apiKey])
            ->post("{$this->baseUrl}/vectors/upsert", [
                'vectors' => [[
                    'id'       => $id,
                    'values'   => $embedding,
                    'metadata' => [
                        'tenant_id' => $tenantId,
                        'content'   => mb_substr($content, 0, 1000),
                    ],
                ]],
            ]);
    }

    public function search(string $tenantId, array $embedding, int $topK = 3): string
    {
        if (!$this->apiKey || !$this->baseUrl) return '';

        $response = Http::withHeaders(['Api-Key' => $this->apiKey])
            ->post("{$this->baseUrl}/query", [
                'vector'          => $embedding,
                'topK'            => $topK,
                'includeMetadata' => true,
                'filter'          => ['tenant_id' => ['$eq' => $tenantId]],
            ])->json();

        return collect($response['matches'] ?? [])
            ->filter(fn($m) => ($m['score'] ?? 0) > 0.7)
            ->pluck('metadata.content')
            ->implode("\n---\n");
    }

    public function delete(string $id): void
    {
        if (!$this->apiKey || !$this->baseUrl) return;

        Http::withHeaders(['Api-Key' => $this->apiKey])
            ->post("{$this->baseUrl}/vectors/delete", ['ids' => [$id]]);
    }
}
