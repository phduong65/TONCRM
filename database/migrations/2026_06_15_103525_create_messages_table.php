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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->onDelete('cascade');
            $table->string('sender_type'); // customer, staff, ai_agent
            $table->string('sender_id');
            $table->string('message_type')->default('text'); // text, image, file, sticker
            $table->text('content');
            $table->json('payload')->nullable();
            $table->string('platform_message_id')->nullable()->unique();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
