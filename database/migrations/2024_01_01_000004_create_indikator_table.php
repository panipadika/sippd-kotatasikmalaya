<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urusan_id')->constrained('urusan')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('indikator')->nullOnDelete();
            $table->string('kode_indikator', 100)->nullable();
            $table->text('nama_indikator');
            $table->text('deskripsi')->nullable();
            $table->string('satuan', 100)->nullable();
            $table->string('tipe_data', 50)->default('numerik'); // numerik, teks, persentase
            $table->integer('urutan')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['urusan_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator');
    }
};
