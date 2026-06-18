<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // subject_id và causer_id mặc định là bigint từ nullableMorphs()
        // Cần đổi sang varchar(36) để lưu UUID của các model như Conversation, Contact, v.v.
        DB::statement('ALTER TABLE activity_log ALTER COLUMN subject_id TYPE varchar(36) USING subject_id::varchar');
        DB::statement('ALTER TABLE activity_log ALTER COLUMN causer_id TYPE varchar(36) USING causer_id::varchar');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_log ALTER COLUMN subject_id TYPE bigint USING subject_id::bigint');
        DB::statement('ALTER TABLE activity_log ALTER COLUMN causer_id TYPE bigint USING causer_id::bigint');
    }
};
