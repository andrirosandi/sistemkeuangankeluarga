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
        Schema::create('request_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('request_header')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 4);
            $table->enum('status', ['pending', 'realized', 'closed'])->nullable()->default(null)->comment('NULL=ikut header, pending=menunggu realisasi, realized=sudah jadi transaction, closed=write off');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_detail');
    }
};
