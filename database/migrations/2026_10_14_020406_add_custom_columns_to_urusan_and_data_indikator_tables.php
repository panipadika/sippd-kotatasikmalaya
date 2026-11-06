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
        if (!Schema::hasColumn('urusan', 'custom_columns')) {
            Schema::table('urusan', function (Blueprint $table) {
                $table->json('custom_columns')->nullable()->after('deskripsi');
            });
        }

        if (!Schema::hasColumn('indikator', 'custom_fields')) {
            Schema::table('indikator', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('deskripsi');
            });
        }

        if (!Schema::hasColumn('data_indikator', 'custom_fields')) {
            Schema::table('data_indikator', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('sumber_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('urusan', 'custom_columns')) {
            Schema::table('urusan', function (Blueprint $table) {
                $table->dropColumn('custom_columns');
            });
        }

        if (Schema::hasColumn('indikator', 'custom_fields')) {
            Schema::table('indikator', function (Blueprint $table) {
                $table->dropColumn('custom_fields');
            });
        }

        if (Schema::hasColumn('data_indikator', 'custom_fields')) {
            Schema::table('data_indikator', function (Blueprint $table) {
                $table->dropColumn('custom_fields');
            });
        }
    }
};
