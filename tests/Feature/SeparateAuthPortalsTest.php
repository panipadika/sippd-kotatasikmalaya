<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Hash;

class SeparateAuthPortalsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_superadmin_portal_page_loads_and_authenticates_with_username()
    {
        $response = $this->get('/superadmin/login');
        $response->assertStatus(200);
        $response->assertSee('PORTAL SUPER ADMIN');
        $response->assertSee('Username Super Admin');

        // Test failed login
        $failedResponse = $this->post('/superadmin/login', [
            'username' => 'superadmin',
            'password' => 'wrongpassword',
        ]);
        $failedResponse->assertSessionHasErrors('username');
        $this->assertGuest();

        // Test successful login
        $loginResponse = $this->post('/superadmin/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('super_admin', auth()->user()->role);
    }

    public function test_admin_portal_page_loads_and_authenticates_with_username()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('PORTAL ADMINISTRATOR DATA');
        $response->assertSee('Username Admin');

        // Regular user cannot login to admin portal
        $userAttempt = $this->post('/admin/login', [
            'username' => 'user',
            'password' => 'password',
        ]);
        $userAttempt->assertSessionHasErrors('username');
        $this->assertGuest();

        // Admin login succeeds
        $loginResponse = $this->post('/admin/login', [
            'username' => 'admin',
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

    public function test_admin_can_access_settings_and_update_login_slugs()
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/pengaturan');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan URL Portal Login');
        $response->assertSee('superadmin');
        $response->assertSee('admin');
        $response->assertSee('portal');

        // Update slugs
        $updateResponse = $this->actingAs($admin)->put('/pengaturan/slugs', [
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
}
