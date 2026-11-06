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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        \Illuminate\Support\Facades\DB::table('app_settings')->insert([
            [
                'key' => 'superadmin_login_slug',
                'value' => 'superadmin',
                'group' => 'auth',
                'description' => 'URL slug untuk portal login Super Admin (contoh: superadmin → /superadmin/login)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'admin_login_slug',
                'value' => 'admin',
                'group' => 'auth',
                'description' => 'URL slug untuk portal login Admin (contoh: admin → /admin/login)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'user_login_slug',
                'value' => 'portal',
                'group' => 'auth',
                'description' => 'URL slug untuk portal login User (contoh: portal → /portal/login)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
