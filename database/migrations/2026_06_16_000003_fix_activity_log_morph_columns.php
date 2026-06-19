<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Đổi subject_id và causer_id sang varchar(36) để lưu UUID
        // MySQL syntax — khác với PostgreSQL (ALTER COLUMN ... TYPE ... USING ...)
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN subject_id varchar(36) NULL');
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN causer_id varchar(36) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN subject_id bigint UNSIGNED NULL');
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN causer_id bigint UNSIGNED NULL');
    }
};
