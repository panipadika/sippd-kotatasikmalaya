<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Akses Penuh: Kelola Pengguna, Master Struktur, Master Tahun, Audit Log)
        $superAdminPassword = env('SUPERADMIN_DEFAULT_PASSWORD', 'password');
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'password');
        $userPassword = env('USER_DEFAULT_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => 'superadmin@datasektoral.go.id'],
            [
                'name' => 'Super Administrator Sistem',
                'username' => 'superadmin',
                'password' => Hash::make($superAdminPassword),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Admin (Pengelola Data Sektoral: Input Data, Indikator, Tabel, Impor/Ekspor Excel, Unduh PDF)
        User::updateOrCreate(
            ['email' => 'admin@datasektoral.go.id'],
            [
                'name' => 'Administrator Data Sektoral',
                'username' => 'admin',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // 3. User (Pengguna Biasa / Viewer: Lihat Dashboard, Lihat Urusan & Data, Unduh PDF & Ekspor Excel)
        User::updateOrCreate(
            ['email' => 'user@datasektoral.go.id'],
            [
                'name' => 'Pengguna Profil Daerah',
                'username' => 'user',
                'password' => Hash::make($userPassword),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Migrasi akun demo lama jika ada
        User::where('email', 'operator@datasektoral.go.id')->update(['role' => 'user']);
    }
}
