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
        Schema::create('transaction_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('transaction_header')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 4)->comment('Amount aktual (bisa beda dari request)');
            $table->foreignId('request_detail_id')->nullable()->constrained('request_detail')->restrictOnDelete()->comment('NULL jika dari template');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_detail');
    }
};
