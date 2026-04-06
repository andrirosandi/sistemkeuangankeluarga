<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus status 'canceled' dari enum transaction_header.status
     * karena tidak digunakan di business logic manapun.
     * Flow aktual: draft ⇄ completed, delete hanya dari draft.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transaction_header MODIFY COLUMN status ENUM('draft', 'completed') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaction_header MODIFY COLUMN status ENUM('draft', 'completed', 'canceled') NOT NULL DEFAULT 'draft'");
    }
};
