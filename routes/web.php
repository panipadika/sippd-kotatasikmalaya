<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UrusanController;
use App\Http\Controllers\KelompokUrusanController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\DataNilaiController;
use App\Http\Controllers\TahunController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingController;
use App\Models\AppSetting;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// ROOT — Redirect ke portal login user
// ====================================================
Route::get('/', [AuthController::class, 'showLogin'])->name('home');

// Backward-compatible /login route — redirect to user portal
Route::get('/login', function () {
    try {
        $slug = AppSetting::getUserSlug();
    } catch (\Throwable $e) {
        $slug = 'portal';
    }
    return redirect("/{$slug}/login");
})->name('login');

// ====================================================
// DYNAMIC AUTH PORTALS
// Routes are registered using dynamic slugs from app_settings.
// A catch-all wildcard pattern also handles login for any slug.
// ====================================================

// Helper: register auth routes for a given slug prefix
$registerAuthRoutes = function () {
    // Super Admin Portal
    try {
        $saSlug = AppSetting::getSuperAdminSlug();
    } catch (\Throwable $e) {
        $saSlug = 'superadmin';
    }

    Route::prefix($saSlug)->group(function () {
        Route::get('/login', [AuthController::class, 'showSuperAdminLogin'])->name('superadmin.login');
        Route::post('/login', [AuthController::class, 'superAdminLogin'])->middleware('throttle:5,1')->name('superadmin.login.post');
    });

    // Admin Portal
    try {
        $adminSlug = AppSetting::getAdminSlug();
    } catch (\Throwable $e) {
        $adminSlug = 'admin';
    }

    Route::prefix($adminSlug)->group(function () {
        Route::get('/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'adminLogin'])->middleware('throttle:5,1')->name('admin.login.post');
    });

    // User Portal
    try {
        $userSlug = AppSetting::getUserSlug();
    } catch (\Throwable $e) {
        $userSlug = 'portal';
    }

    Route::prefix($userSlug)->group(function () {
        Route::get('/login', [AuthController::class, 'showUserLogin'])->name('user.login');
        Route::post('/login', [AuthController::class, 'userLogin'])->middleware('throttle:5,1')->name('user.login.post');
        Route::get('/register', [AuthController::class, 'showUserRegister'])->name('user.register');
        Route::post('/register', [AuthController::class, 'userRegister'])->middleware('throttle:3,1')->name('user.register.post');
    });
};

$registerAuthRoutes();

// ====================================================
// AUTHENTICATED ROUTES
// ====================================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profil', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profil', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Quick switch year
    Route::post('/set-tahun', function (Request $request) {
        $tahun = $request->validate(['tahun' => 'required|integer']);
        session(['selected_tahun' => (int) $tahun['tahun']]);
        return back();
    })->name('set-tahun');

    // ==========================================
    // 1. UMUM / SEMUA ROLE (Super Admin, Admin, User)
    // ==========================================
    // Dashboard Ringkasan Realtime
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Nilai Tahunan (Tampilan Read-only untuk User)
    Route::get('/data', [DataNilaiController::class, 'index'])->name('data.index');

    // Detail Urusan (Katalog dinonaktifkan, dialihkan ke Dashboard)
    Route::get('/urusan', [UrusanController::class, 'index'])->name('urusan.index');
    Route::get('/bidang/{id}', [KelompokUrusanController::class, 'show'])->name('kelompok-urusan.show');
    Route::get('/urusan/{id}', [UrusanController::class, 'show'])->name('urusan.show');
    Route::get('/urusan/{id}/pdf', [UrusanController::class, 'exportPdf'])->name('urusan.pdf');
    Route::get('/urusan/{id}/excel', [UrusanController::class, 'exportExcel'])->name('urusan.excel.export');
    Route::get('/urusan/{id}/excel/template', [UrusanController::class, 'downloadTemplate'])->name('urusan.excel.template');

    // Detail Indikator (View)
    Route::get('/indikator/{id}', [IndikatorController::class, 'show'])->name('indikator.show');

    // Tahun Anggaran (Lihat Daftar Tahun)
    Route::get('/tahun', [TahunController::class, 'index'])->name('tahun.index');

    // ==========================================
    // 2. ADMIN & SUPER ADMIN (role:admin)
    // Hak Akses Operasional & Pengelolaan Data Sektoral
    // ==========================================
    Route::middleware('role:admin')->group(function () {
        // Pengisian & Update Data Nilai
        Route::post('/data/single', [DataNilaiController::class, 'updateSingle'])->name('data.single');
        Route::post('/data/batch', [DataNilaiController::class, 'batchUpdate'])->name('data.batch');

        // Pengelolaan Indikator & Multi-Tabel pada Urusan
        Route::post('/indikator', [IndikatorController::class, 'store'])->name('indikator.store');
        Route::put('/indikator/{id}', [IndikatorController::class, 'update'])->name('indikator.update');
        Route::post('/indikator/{id}/cell', [IndikatorController::class, 'updateCell'])->name('indikator.update-cell');
        Route::post('/indikator/{id}/sub-rows', [IndikatorController::class, 'storeSubRows'])->name('indikator.sub-rows');
        Route::post('/urusan/{id}/quick-row', [IndikatorController::class, 'quickCreateRow'])->name('urusan.quick-row');
        Route::post('/indikator/{id}/toggle-status', [IndikatorController::class, 'toggleStatus'])->name('indikator.toggle-status');
        Route::delete('/indikator/{id}', [IndikatorController::class, 'destroy'])->name('indikator.destroy');

        Route::post('/urusan/{id}/columns', [UrusanController::class, 'updateColumns'])->name('urusan.columns');
        Route::post('/urusan/{id}/table', [UrusanController::class, 'storeTable'])->name('urusan.table.store');
        Route::delete('/urusan/{id}/table/{tableId}', [UrusanController::class, 'destroyTable'])->name('urusan.table.destroy');
        Route::post('/urusan/{id}/excel/import', [UrusanController::class, 'importExcel'])->name('urusan.excel.import');

    });

    // ==========================================
    // 3. KHUSUS SUPER ADMIN (role:super_admin)
    // Hak Akses Penuh: User, Master Data, Master Tahun, Audit Log
    // ==========================================
    Route::middleware('role:super_admin')->group(function () {
        // Manajemen Master Kelompok Urusan (Folder) & Urusan
        Route::get('/urusan-baru/tambah', [UrusanController::class, 'create'])->name('urusan.create');
        Route::get('/urusan/{id}/edit', [UrusanController::class, 'edit'])->name('urusan.edit');
        Route::post('/urusan', [UrusanController::class, 'store'])->name('urusan.store');
        Route::put('/urusan/{id}', [UrusanController::class, 'update'])->name('urusan.update');
        Route::delete('/urusan/{id}', [UrusanController::class, 'destroy'])->name('urusan.destroy');

        Route::post('/kelompok-urusan', [KelompokUrusanController::class, 'store'])->name('kelompok-urusan.store');
        Route::put('/kelompok-urusan/{id}', [KelompokUrusanController::class, 'update'])->name('kelompok-urusan.update');
        Route::delete('/kelompok-urusan/{id}', [KelompokUrusanController::class, 'destroy'])->name('kelompok-urusan.destroy');

        // Manajemen Master Tahun Anggaran
        Route::post('/tahun', [TahunController::class, 'store'])->name('tahun.store');
        Route::post('/tahun/{id}/default', [TahunController::class, 'setDefault'])->name('tahun.set-default');
        Route::post('/tahun/{id}/toggle-status', [TahunController::class, 'toggleStatus'])->name('tahun.toggle-status');
        Route::delete('/tahun/{id}', [TahunController::class, 'destroy'])->name('tahun.destroy');

        // Manajemen Pengguna Sistem
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Log Aktivitas & Audit Trail
        Route::get('/aktivitas', [ActivityLogController::class, 'index'])->name('activity.index');

        // Pengaturan Sistem / Portal Login (Khusus Super Admin)
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/pengaturan/slugs', [SettingController::class, 'updateSlugs'])->name('settings.update-slugs');
    });
});
