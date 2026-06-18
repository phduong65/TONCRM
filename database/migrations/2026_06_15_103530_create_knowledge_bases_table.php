<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(\DB::raw('gen_random_uuid()'));
            $table->foreignUuid('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('source_url')->nullable();
            $table->string('source_type')->default('manual'); // manual, url, file
            $table->timestamps();

            $table->index('tenant_id');
        });

        // Thêm vector column chỉ khi PostgreSQL + pgvector đã cài
        if (\DB::getDriverName() === 'pgsql') {
            $vectorInstalled = \DB::selectOne("SELECT 1 FROM pg_available_extensions WHERE name = 'vector' AND installed_version IS NOT NULL");
            if ($vectorInstalled) {
                \DB::statement('ALTER TABLE knowledge_bases ADD COLUMN IF NOT EXISTS embedding vector(1536)');
                \DB::statement('CREATE INDEX IF NOT EXISTS kb_embedding_idx ON knowledge_bases USING ivfflat (embedding vector_cosine_ops)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
