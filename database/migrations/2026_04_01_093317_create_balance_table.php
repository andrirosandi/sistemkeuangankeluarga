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
        Schema::create('balance', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique()->comment('Format: YYYY-MM');
            $table->decimal('begin', 15, 4)->default(0)->comment('Saldo awal bulan');
            $table->decimal('total_in', 15, 4)->default(0)->comment('Total pemasukan bulan ini');
            $table->decimal('total_out', 15, 4)->default(0)->comment('Total pengeluaran bulan ini');
            $table->decimal('ending', 15, 4)->default(0)->comment('Saldo akhir bulan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance');
    }
};
