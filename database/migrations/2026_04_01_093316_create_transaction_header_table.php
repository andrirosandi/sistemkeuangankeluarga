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
        Schema::create('transaction_header', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description');
            $table->text('notes')->nullable()->comment('Catatan tambahan admin untuk transaksi riil');
            $table->decimal('amount', 15, 4)->comment('Auto-sum dari transaction_detail');
            $table->foreignId('request_id')->nullable()->constrained('request_header')->restrictOnDelete()->comment('NULL jika input langsung dari template');
            $table->unsignedTinyInteger('trans_code')->comment('1=IN, 2=OUT');
            $table->date('transaction_date')->comment('Tanggal realisasi transaksi');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->comment('Admin yang membuat');
            $table->enum('status', ['draft', 'completed', 'canceled'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_header');
    }
};
