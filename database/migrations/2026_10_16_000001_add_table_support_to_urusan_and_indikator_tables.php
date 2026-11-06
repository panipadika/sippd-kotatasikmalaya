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
        if (!Schema::hasColumn('urusan', 'table_configs')) {
            Schema::table('urusan', function (Blueprint $table) {
                $table->json('table_configs')->nullable()->after('custom_columns');
            });
        }

        if (!Schema::hasColumn('indikator', 'table_id')) {
            Schema::table('indikator', function (Blueprint $table) {
                $table->unsignedInteger('table_id')->default(1)->after('urusan_id');
                $table->index(['urusan_id', 'table_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('urusan', 'table_configs')) {
            Schema::table('urusan', function (Blueprint $table) {
                $table->dropColumn('table_configs');
            });
        }

        if (Schema::hasColumn('indikator', 'table_id')) {
            Schema::table('indikator', function (Blueprint $table) {
                $table->dropIndex(['urusan_id', 'table_id']);
                $table->dropColumn('table_id');
            });
        }
    }
};
