<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class SecurityHardeningVaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Artisan::call('migrate');
    }

    public function test_login_forms_do_not_contain_hardcoded_passwords(): void
    {
        $portals = ['/admin/login', '/superadmin/login', '/portal/login'];

        foreach ($portals as $portal) {
            $response = $this->get($portal);
            $response->assertStatus(200);
            $this->assertStringNotContainsString('value="password"', $response->getContent());
        }

        // Test root login redirect follows to portal login without hardcoded password
        $redirectResponse = $this->get('/login');
        $redirectResponse->assertRedirect('/portal/login');
    }

    public function test_indicator_not_found_returns_generic_404_json(): void
    {
        $user = User::first() ?: User::create([
            'name' => 'Test User',
            'email' => 'test_sec@datasektoral.go.id',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/indikator/999999999');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Indikator tidak ditemukan.',
        ]);

        // Pastikan tidak ada kebocoran stack trace / exception class
        $this->assertStringNotContainsString('ModelNotFoundException', $response->getContent());
        $this->assertStringNotContainsString('trace', $response->getContent());
    }

    public function test_update_cell_rejects_non_numeric_for_numeric_field(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::create([
            'name' => 'Admin Sec',
            'email' => 'admin_sec@datasektoral.go.id',
            'username' => 'admin_sec',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Tahun::firstOrCreate(['tahun' => 2026], [
            'is_default' => true,
            'status_aktif' => true,
            'keterangan' => 'Tahun Anggaran 2026',
        ]);

        $kelompok = KelompokUrusan::create(['kode' => 'A', 'nama' => 'Pendidikan', 'urutan' => 1]);
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $kelompok->id,
            'kode_urusan' => '1.01',
            'slug' => 'urusan-pendidikan',
            'nama_urusan' => 'Urusan Pendidikan',
            'status_aktif' => true,
        ]);
        $indikator = Indikator::create([
            'urusan_id' => $urusan->id,
            'kode_indikator' => 'IND-01',
            'nama_indikator' => 'Jumlah Murid',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->postJson("/indikator/{$indikator->id}/cell", [
            'field' => 'col_nilai',
            'value' => 'VA_INVALID_NON_NUMERIC',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_profile_update_password_requires_current_password(): void
    {
        $user = User::create([
            'name' => 'Profile Test User',
            'email' => 'profile_test_' . time() . '@datasektoral.go.id',
            'password' => Hash::make('OldPassword123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Attempt password change without current_password
        $response = $this->actingAs($user)->put('/profil', [
            'name' => 'Profile Test User',
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHasErrors('current_password');

        // Verify password did NOT change
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_deactivated_user_cannot_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive_' . time() . '@datasektoral.go.id',
            'password' => Hash::make('Password123'),
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        // Should be logged out and redirected to login with error
        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_failed_login_records_audit_log(): void
    {
        $initialCount = ActivityLog::where('action', 'failed_login')->count();

        $this->post('/portal/login', [
            'email' => 'nonexistent_account_' . time() . '@example.com',
            'password' => 'wrongpass',
        ]);

        $newCount = ActivityLog::where('action', 'failed_login')->count();
        $this->assertGreaterThan($initialCount, $newCount);
    }

    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/portal/login');
        $response->assertStatus(200);

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_database_backup_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('db:backup', ['--keep' => 5]);
        $this->assertEquals(0, $exitCode);

        $backupDir = storage_path('app/backups');
        $this->assertTrue(File::isDirectory($backupDir));

        $backupFiles = File::glob("{$backupDir}/sippd_backup_*.sql*");
        $this->assertNotEmpty($backupFiles);

        // Verify ActivityLog recorded the backup
        $this->assertTrue(
            ActivityLog::where('action', 'database_backup')->exists()
        );

        // Cleanup test backup files
        foreach ($backupFiles as $file) {
            File::delete($file);
        }
    }

    public function test_prune_activity_logs_command_cleans_old_records(): void
    {
        // Insert old activity log (e.g., 200 days ago)
        $oldLog = ActivityLog::create([
            'user_id' => null,
            'action' => 'old_test_action',
            'description' => 'Log dari 200 hari lalu',
            'ip_address' => '127.0.0.1',
        ]);
        $oldLog->created_at = now()->subDays(200);
        $oldLog->save();

        // Insert fresh activity log
        $freshLog = ActivityLog::create([
            'user_id' => null,
            'action' => 'fresh_test_action',
            'description' => 'Log baru hari ini',
            'ip_address' => '127.0.0.1',
        ]);

        $exitCode = Artisan::call('activity-log:prune', ['--days' => 180]);
        $this->assertEquals(0, $exitCode);

        // Old log should be deleted, fresh log retained
        $this->assertNull(ActivityLog::find($oldLog->id));
        $this->assertNotNull(ActivityLog::find($freshLog->id));

        // Cleanup fresh log
        $freshLog->delete();
    }
}
