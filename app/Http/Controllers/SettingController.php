<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = AppSetting::getGroup('auth');

        return view('settings.index', [
            'settings' => $settings,
            'superadminSlug' => AppSetting::getSuperAdminSlug(),
            'adminSlug' => AppSetting::getAdminSlug(),
            'userSlug' => AppSetting::getUserSlug(),
        ]);
    }

    /**
     * Update the URL slug settings.
     */
    public function updateSlugs(Request $request)
    {
        $request->validate([
            'superadmin_login_slug' => ['required', 'string', 'max:50', 'alpha_dash', 'regex:/^[a-z0-9\-]+$/'],
            'admin_login_slug' => ['required', 'string', 'max:50', 'alpha_dash', 'regex:/^[a-z0-9\-]+$/'],
            'user_login_slug' => ['required', 'string', 'max:50', 'alpha_dash', 'regex:/^[a-z0-9\-]+$/'],
        ], [
            'superadmin_login_slug.required' => 'Slug portal Super Admin wajib diisi.',
            'superadmin_login_slug.alpha_dash' => 'Slug hanya boleh huruf kecil, angka, dan dash.',
            'admin_login_slug.required' => 'Slug portal Admin wajib diisi.',
            'admin_login_slug.alpha_dash' => 'Slug hanya boleh huruf kecil, angka, dan dash.',
            'user_login_slug.required' => 'Slug portal User wajib diisi.',
            'user_login_slug.alpha_dash' => 'Slug hanya boleh huruf kecil, angka, dan dash.',
        ]);

        // Ensure all slugs are unique
        $slugs = [
            $request->superadmin_login_slug,
            $request->admin_login_slug,
            $request->user_login_slug,
        ];

        if (count($slugs) !== count(array_unique($slugs))) {
            return back()->with('error', 'Setiap portal harus memiliki URL slug yang berbeda.');
        }

        // Check reserved slugs
        $reserved = ['login', 'logout', 'register', 'dashboard', 'urusan', 'indikator', 'data', 'tahun', 'users', 'aktivitas', 'profil', 'api', 'set-tahun', 'kelompok-urusan', 'urusan-baru', 'pengaturan'];
        foreach ($slugs as $slug) {
            if (in_array($slug, $reserved)) {
                return back()->with('error', "Slug '{$slug}' sudah dipakai oleh sistem. Pilih slug lain.");
            }
        }

        AppSetting::setValue('superadmin_login_slug', strtolower($request->superadmin_login_slug));
        AppSetting::setValue('admin_login_slug', strtolower($request->admin_login_slug));
        AppSetting::setValue('user_login_slug', strtolower($request->user_login_slug));

        try {
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        } catch (\Throwable $e) {
            // Ignore if artisan cache clear is unavailable in certain environments
        }

        ActivityLog::record('update_settings', 'Memperbarui pengaturan URL slug portal login');

        return back()->with('success', 'Pengaturan URL slug berhasil diperbarui. Perubahan langsung berlaku.');
    }
}
