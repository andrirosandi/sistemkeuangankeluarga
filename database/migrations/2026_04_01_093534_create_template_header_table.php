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
        Schema::create('template_header', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description');
            $table->decimal('amount', 15, 4);
            $table->unsignedTinyInteger('trans_code')->comment('1=IN, 2=OUT');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->comment('Admin yang membuat template');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_header');
    }
};
