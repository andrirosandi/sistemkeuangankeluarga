<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah semua data 'canceled' menjadi 'draft' sebelum alter enum
        DB::table('request_header')
            ->where('status', 'canceled')
            ->update(['status' => 'draft']);

        // Alter enum: hapus 'canceled'
        DB::statement("ALTER TABLE request_header MODIFY COLUMN status ENUM('draft', 'requested', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE request_header MODIFY COLUMN status ENUM('draft', 'requested', 'approved', 'rejected', 'canceled') NOT NULL DEFAULT 'draft'");
    }
};
