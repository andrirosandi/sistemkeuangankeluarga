<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel role_visibility — mengatur role mana yang boleh
     * melihat data milik role lain (cross-role data access).
     * 
     * Admin tidak perlu dimasukkan di sini — langsung bypass via kode.
     */
    public function up(): void
    {
        Schema::create('role_visibility', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('watcher_role_id')->comment('FK roles: role yang MELIHAT data');
            $table->unsignedBigInteger('watched_role_id')->comment('FK roles: role yang DILIHAT datanya');
            $table->unsignedBigInteger('created_by')->nullable()->comment('FK users: admin yang mengatur');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('FK users: admin yang terakhir mengubah');
            $table->timestamps();

            // Constraint: satu pasangan watcher-watched hanya boleh ada 1x
            $table->unique(['watcher_role_id', 'watched_role_id'], 'unique_visibility_pair');

            // Foreign keys
            $table->foreign('watcher_role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('watched_role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_visibility');
    }
};
