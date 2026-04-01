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
        Schema::create('request_header', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description');
            $table->text('notes')->nullable()->comment('Catatan tambahan/penjelasan opsional dari user');
            $table->decimal('amount', 15, 4)->comment('Auto-sum dari request_detail');
            $table->unsignedTinyInteger('trans_code')->comment('1=IN, 2=OUT');
            $table->date('request_date')->comment('Tanggal pengajuan/pengeluaran');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->comment('User yang mengajukan');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal')->comment('Tingkat urgensi pengajuan');
            $table->enum('status', ['draft', 'requested', 'approved', 'rejected', 'canceled'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete()->comment('Admin yang approve/reject');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approve/reject');
            $table->text('rejection_reason')->nullable()->comment('Alasan jika di-reject');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_header');
    }
};
