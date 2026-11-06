<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->foreignId('tahun_id')->constrained('tahun')->cascadeOnDelete();
            $table->string('nilai', 255)->nullable();
            $table->string('sumber_data', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['indikator_id', 'tahun_id'], 'unique_indikator_tahun');
            $table->index(['indikator_id', 'tahun_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_indikator');
    }
};
