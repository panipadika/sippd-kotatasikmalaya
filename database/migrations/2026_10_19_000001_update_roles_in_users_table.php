<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah default role menjadi 'user'
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });

        // Migrasi data pengguna lama:
        // 'operator' diubah menjadi 'user'
        DB::table('users')
            ->where('role', 'operator')
            ->update(['role' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->change();
        });

        DB::table('users')
            ->where('role', 'user')
            ->update(['role' => 'operator']);
    }
};
