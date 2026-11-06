<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_urusan_id')->constrained('kelompok_urusan')->cascadeOnDelete();
            $table->integer('nomor')->nullable(); // Nomor 1 - 28 untuk kelompok A - C
            $table->string('kode_urusan', 50)->nullable();
            $table->string('nama_urusan', 255);
            $table->string('slug', 255)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('icon', 100)->default('bi-folder2');
            $table->integer('urutan')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urusan');
    }
};
