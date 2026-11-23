<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SeparateAuthPortalsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');

        // Ensure test users exist with known credentials
        User::updateOrCreate(
            ['email' => 'superadmin@datasektoral.go.id'],
            [
                'name' => 'Super Administrator Sistem',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@datasektoral.go.id'],
            [
                'name' => 'Administrator Data Sektoral',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@datasektoral.go.id'],
            [
                'name' => 'Pengguna Profil Daerah',
                'username' => 'user',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Reset default slugs
        AppSetting::setValue('superadmin_login_slug', 'superadmin');
        AppSetting::setValue('admin_login_slug', 'admin');
        AppSetting::setValue('user_login_slug', 'portal');
    }

    public function test_superadmin_portal_page_loads_and_authenticates_with_email()
    {
        $response = $this->get('/superadmin/login');
        $response->assertStatus(200);
        $response->assertSee('PORTAL SUPER ADMIN');
        $response->assertSee('Alamat Email');

        // Test failed login
        $failedResponse = $this->post('/superadmin/login', [
            'email' => 'superadmin@datasektoral.go.id',
            'password' => 'wrongpassword',
        ]);
        $failedResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Test successful login with email
        $loginResponse = $this->post('/superadmin/login', [
            'email' => 'superadmin@datasektoral.go.id',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('super_admin', auth()->user()->role);
    }

    public function test_admin_portal_page_loads_and_authenticates_with_email()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('PORTAL ADMINISTRATOR DATA');
        $response->assertSee('Alamat Email');

        // Regular user cannot login to admin portal
        $userAttempt = $this->post('/admin/login', [
            'email' => 'user@datasektoral.go.id',
            'password' => 'password',
        ]);
        $userAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // Admin login succeeds with email
        $loginResponse = $this->post('/admin/login', [
            'email' => 'admin@datasektoral.go.id',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_user_portal_page_loads_and_authenticates_with_email()
    {
        $response = $this->get('/portal/login');
        $response->assertStatus(200);
        $response->assertSee('SIPPD KOTA TASIKMALAYA');
        $response->assertSee('Alamat Email');
        $response->assertSee('Daftar Akun Baru Sekarang');

        // User login succeeds with email
        $loginResponse = $this->post('/portal/login', [
            'email' => 'user@datasektoral.go.id',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('user', auth()->user()->role);
    }

    public function test_user_can_register_new_account()
    {
        $response = $this->get('/portal/register');
        $response->assertStatus(200);
        $response->assertSee('Buat Akun SIPPD');

        $uniqueEmail = 'newuser_' . time() . '@test.com';
        $registerResponse = $this->post('/portal/register', [
            'name' => 'Warga Tasikmalaya',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => 'user',
        ]);
    }

    public function test_superadmin_can_access_settings_and_update_login_slugs()
    {
        $superadmin = User::where('username', 'superadmin')->first();

        $response = $this->actingAs($superadmin)->get('/pengaturan');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan URL Portal Login');
        $response->assertSee('superadmin');
        $response->assertSee('admin');
        $response->assertSee('portal');

        // Update slugs
        $updateResponse = $this->actingAs($superadmin)->put('/pengaturan/slugs', [
            'superadmin_login_slug' => 'custom-superadmin',
            'admin_login_slug' => 'custom-admin',
            'user_login_slug' => 'custom-user',
        ]);
        $updateResponse->assertSessionHas('success');

        $this->assertEquals('custom-superadmin', AppSetting::getSuperAdminSlug());
        $this->assertEquals('custom-admin', AppSetting::getAdminSlug());
        $this->assertEquals('custom-user', AppSetting::getUserSlug());

        // Restore default slugs
        AppSetting::setValue('superadmin_login_slug', 'superadmin');
        AppSetting::setValue('admin_login_slug', 'admin');
        AppSetting::setValue('user_login_slug', 'portal');
    }

    public function test_admin_can_update_profile_and_username()
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->put('/profil', [
            'name' => 'Admin Updated Name',
            'username' => 'admin_updated',
            'email' => 'admin@datasektoral.go.id',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Updated Name',
            'username' => 'admin_updated',
        ]);

        // Reset username back to admin
        $admin->update(['username' => 'admin', 'name' => 'Administrator Data Sektoral']);
    }

    public function test_regular_user_can_update_profile_and_username()
    {
        $user = User::where('role', 'user')->first();

        $response = $this->actingAs($user)->put('/profil', [
            'name' => 'User Updated Name',
            'username' => 'user_custom_handle',
            'email' => 'user@datasektoral.go.id',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'User Updated Name',
            'username' => 'user_custom_handle',
        ]);

        // Reset
        $user->update(['username' => 'user', 'name' => 'Pengguna Profil Daerah']);
    }

    public function test_superadmin_can_create_new_user_with_and_without_username()
    {
        $superadmin = User::where('role', 'super_admin')->first();

        // 1. Tambah user biasa (tanpa username)
        $responseUser = $this->actingAs($superadmin)->post('/users', [
            'name' => 'Staf Baru Publik',
            'username' => '',
            'email' => 'stafpublik@datasektoral.go.id',
            'password' => 'Password123!',
            'role' => 'user',
        ]);
        $responseUser->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'stafpublik@datasektoral.go.id',
            'role' => 'user',
            'is_active' => true,
        ]);

        // 2. Tambah admin baru (dengan username spesifik)
        $responseAdmin = $this->actingAs($superadmin)->post('/users', [
            'name' => 'Admin Baru Sektoral',
            'username' => 'admin_baru',
            'email' => 'adminbaru@datasektoral.go.id',
            'password' => 'Password123!',
            'role' => 'admin',
        ]);
        $responseAdmin->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@datasektoral.go.id',
            'username' => 'admin_baru',
            'role' => 'admin',
        ]);

        // 3. Tambah admin baru tanpa username (auto-generated)
        $responseAuto = $this->actingAs($superadmin)->post('/users', [
            'name' => 'Budi Santoso',
            'username' => '',
            'email' => 'budisantoso@datasektoral.go.id',
            'password' => 'Password123!',
            'role' => 'admin',
        ]);
        $responseAuto->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'budisantoso@datasektoral.go.id',
            'username' => 'budi_santoso',
        ]);
    }

    public function test_logout_redirects_to_respective_portal_login()
    {
        $superadmin = User::where('username', 'superadmin')->first();
        $response = $this->actingAs($superadmin)->post('/logout');
        $response->assertRedirect('/superadmin/login');

        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->post('/logout');
        $response->assertRedirect('/admin/login');

        $user = User::where('username', 'user')->first();
        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/portal/login');
    }

    public function test_dashboard_pembaruan_terakhir_hidden_for_regular_user()
    {
        $user = User::where('username', 'user')->first();
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Pembaruan Terakhir');
        $response->assertDontSee('Feed data nilai yang baru diperbarui');
        $response->assertSee('col-xl-12');
    }

    public function test_dashboard_pembaruan_terakhir_visible_for_admin()
    {
        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pembaruan Terakhir');
        $response->assertSee('Feed data nilai yang baru diperbarui');
    }

    public function test_forgot_password_page_loads_and_handles_reset_request()
    {
        Mail::fake();

        $response = $this->get('/lupa-password');
        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi');

        // Submit with existing email
        $postResponse = $this->post('/lupa-password', [
            'email' => 'admin@datasektoral.go.id',
            'portal' => 'admin',
        ]);

        $postResponse->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'admin@datasektoral.go.id',
        ]);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $token = 'test-token-12345';
        $email = 'user@datasektoral.go.id';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $showResponse = $this->get("/reset-password/{$token}?email=" . urlencode($email) . "&portal=user");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Atur Ulang Kata Sandi');

        $updateResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => $email,
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
            'portal' => 'user',
        ]);

        $updateResponse->assertRedirect('/portal/login');
        $updateResponse->assertSessionHas('success');

        // Verify password was updated
        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('NewSecurePassword123', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $email,
        ]);
    }

    public function test_forgot_and_reset_password_pages_adapt_to_portal_theme()
    {
        // 1. Super Admin Forgot Password
        $superadminForgot = $this->get('/lupa-password?portal=superadmin');
        $superadminForgot->assertStatus(200);
        $superadminForgot->assertSee('PORTAL SUPER ADMIN');
        $superadminForgot->assertSee('Lupa Kata Sandi - SIPPD Super Admin');
        $superadminForgot->assertSee('#f43f5e');

        // 2. Admin Forgot Password
        $adminForgot = $this->get('/lupa-password?portal=admin');
        $adminForgot->assertStatus(200);
        $adminForgot->assertSee('PORTAL ADMINISTRATOR DATA');
        $adminForgot->assertSee('Lupa Kata Sandi - SIPPD Admin');
        $adminForgot->assertSee('#38bdf8');

        // 3. User Forgot Password
        $userForgot = $this->get('/lupa-password?portal=user');
        $userForgot->assertStatus(200);
        $userForgot->assertDontSee('PORTAL SUPER ADMIN');
        $userForgot->assertDontSee('PORTAL ADMINISTRATOR DATA');
        $userForgot->assertSee('Lupa Kata Sandi - SIPPD Kota Tasikmalaya');
        $userForgot->assertSee('#0d9488');

        // Reset Password tests
        $token = 'portal-theme-token';
        $email = 'superadmin@datasektoral.go.id';

        $superadminReset = $this->get("/reset-password/{$token}?email=" . urlencode($email) . "&portal=superadmin");
        $superadminReset->assertStatus(200);
        $superadminReset->assertSee('PORTAL SUPER ADMIN');
        $superadminReset->assertSee('Atur Ulang Kata Sandi - SIPPD Super Admin');

        $adminReset = $this->get("/reset-password/{$token}?email=" . urlencode($email) . "&portal=admin");
        $adminReset->assertStatus(200);
        $adminReset->assertSee('PORTAL ADMINISTRATOR DATA');
        $adminReset->assertSee('Atur Ulang Kata Sandi - SIPPD Admin');

        $userReset = $this->get("/reset-password/{$token}?email=" . urlencode($email) . "&portal=user");
        $userReset->assertStatus(200);
        $userReset->assertDontSee('PORTAL SUPER ADMIN');
        $userReset->assertDontSee('PORTAL ADMINISTRATOR DATA');
        $userReset->assertSee('Atur Ulang Kata Sandi - SIPPD Kota Tasikmalaya');
    }
}
