<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->unique();
            $table->boolean('status_aktif')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun');
    }
};
