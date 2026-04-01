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
        Schema::create('template_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('template_header')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_detail');
    }
};
