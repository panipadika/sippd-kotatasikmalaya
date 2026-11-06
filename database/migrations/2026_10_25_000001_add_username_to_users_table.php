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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Set default username for existing admin/super_admin users
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'admin'])->get();
        foreach ($admins as $admin) {
            $base = strtolower(str_replace(' ', '_', $admin->name));
            $base = preg_replace('/[^a-z0-9_]/', '', $base);
            if (empty($base)) {
                $base = $admin->role;
            }
            // Ensure uniqueness
            $username = $base;
            $counter = 1;
            while (\App\Models\User::where('username', $username)->where('id', '!=', $admin->id)->exists()) {
                $username = $base . $counter;
                $counter++;
            }
            $admin->username = $username;
            $admin->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
