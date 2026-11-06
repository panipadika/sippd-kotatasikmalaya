<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use Illuminate\Support\Facades\DB;

class DataSektoralSystemTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIPD SEKTORAL');
    }

    /** @test */
    public function admin_can_authenticate()
    {
        $response = $this->post('/login', [
            'email' => 'admin@datasektoral.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function dashboard_shows_real_database_metrics()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Profil Pembangunan Daerah');
        $response->assertSee('Kelompok Urusan');
        $response->assertSee('Urusan Pemerintahan');
    }

    /** @test */
    public function admin_can_add_urusan_from_sidebar_without_optional_fields()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $kelompok = KelompokUrusan::firstOrFail();
        $namaUrusan = 'Urusan Sidebar ' . uniqid();

        $response = $this->actingAs($admin)->post('/urusan', [
            'kelompok_urusan_id' => $kelompok->id,
            'nama_urusan' => $namaUrusan,
        ]);

        $response->assertRedirect();
        $urusan = Urusan::where('nama_urusan', $namaUrusan)->firstOrFail();
        $this->assertSame('bi-folder2', $urusan->icon);
        $this->assertSame(99, $urusan->urutan);
    }

    /** @test */
    public function editing_urusan_name_without_status_field_preserves_active_status_and_sidebar_visibility()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $group = KelompokUrusan::create([
            'kode' => 'E' . substr(uniqid(), -5),
            'nama' => 'Kelompok Edit Status Uji',
            'urutan' => 999,
        ]);
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $group->id,
            'nama_urusan' => 'sd',
            'slug' => 'sd-edit-status-' . uniqid(),
            'icon' => 'bi-folder2',
            'urutan' => 1,
            'status_aktif' => true,
        ]);

        try {
            $response = $this->actingAs($admin)->put(route('urusan.update', $urusan->id), [
                'kelompok_urusan_id' => $group->id,
                'nama_urusan' => 'sma',
                'nomor' => 1,
                'icon' => 'bi-folder2',
            ]);

            $response->assertRedirect(route('urusan.show', $urusan->id));
            $this->assertDatabaseHas('urusan', [
                'id' => $urusan->id,
                'nama_urusan' => 'sma',
                'status_aktif' => true,
            ]);
            $this->actingAs($admin)->get(route('dashboard'))
                ->assertOk()
                ->assertSee('sma');
        } finally {
            DB::table('urusan')->where('id', $urusan->id)->delete();
            DB::table('kelompok_urusan')->where('id', $group->id)->delete();
        }
    }

    /** @test */
    public function admin_can_delete_urusan_with_indicators_and_annual_data()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $kelompok = KelompokUrusan::firstOrFail();
        $tahun = Tahun::firstOrFail();
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $kelompok->id,
            'nama_urusan' => 'Urusan Hapus Cascade ' . uniqid(),
            'slug' => 'urusan-hapus-cascade-' . uniqid(),
            'icon' => 'bi-folder2',
            'urutan' => 999,
            'status_aktif' => true,
        ]);
        $indikator = Indikator::create([
            'urusan_id' => $urusan->id,
            'nama_indikator' => 'Indikator untuk dihapus',
            'status_aktif' => true,
        ]);
        $subindikator = Indikator::create([
            'urusan_id' => $urusan->id,
            'parent_id' => $indikator->id,
            'nama_indikator' => 'Subindikator soft deleted',
            'status_aktif' => true,
        ]);
        $subindikator->delete();

        $data = DataIndikator::create([
            'indikator_id' => $indikator->id,
            'tahun_id' => $tahun->id,
            'nilai' => '123',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('urusan.show', $urusan->slug))
            ->delete(route('urusan.destroy', $urusan->id));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('urusan', ['id' => $urusan->id]);
        $this->assertDatabaseMissing('indikator', ['id' => $indikator->id]);
        $this->assertDatabaseMissing('indikator', ['id' => $subindikator->id]);
        $this->assertDatabaseMissing('data_indikator', ['id' => $data->id]);
    }

    /** @test */
    public function sidebar_delete_returns_json_without_reloading_the_application_shell()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $kelompok = KelompokUrusan::firstOrFail();
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $kelompok->id,
            'nama_urusan' => 'Urusan Hapus AJAX ' . uniqid(),
            'slug' => 'urusan-hapus-ajax-' . uniqid(),
            'icon' => 'bi-folder2',
            'urutan' => 999,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($admin)->deleteJson(route('urusan.destroy', $urusan->id));

        $response->assertOk()
            ->assertJsonPath('message', 'Urusan "' . $urusan->nama_urusan . '" beserta indikator dan data tahunannya berhasil dihapus.');
        $this->assertDatabaseMissing('urusan', ['id' => $urusan->id]);
    }

    /** @test */
    public function admin_can_edit_and_delete_a_populated_kelompok_urusan()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $tahun = Tahun::firstOrFail();
        $kode = 'T' . substr(uniqid(), -6);
        $kelompok = KelompokUrusan::create([
            'kode' => $kode,
            'nama' => 'Kelompok Uji ' . uniqid(),
            'urutan' => 999,
        ]);
        $childKelompok = KelompokUrusan::create([
            'parent_id' => $kelompok->id,
            'kode' => 'C' . substr(uniqid(), -5),
            'nama' => 'Folder Anak Uji',
            'urutan' => 1,
        ]);
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $childKelompok->id,
            'nama_urusan' => 'Urusan dalam kelompok uji',
            'slug' => 'urusan-kelompok-uji-' . uniqid(),
            'icon' => 'bi-folder2',
            'urutan' => 1,
            'status_aktif' => true,
        ]);
        $indikator = Indikator::create([
            'urusan_id' => $urusan->id,
            'nama_indikator' => 'Indikator kelompok uji',
            'status_aktif' => true,
        ]);
        $data = DataIndikator::create([
            'indikator_id' => $indikator->id,
            'tahun_id' => $tahun->id,
            'nilai' => '456',
        ]);

        $this->actingAs($admin)->put(route('kelompok-urusan.update', $kelompok->id), [
            'kode' => $kode,
            'nama' => 'Kelompok Uji Diperbarui',
            'keterangan' => 'Keterangan uji',
        ])->assertRedirect();

        $this->assertDatabaseHas('kelompok_urusan', [
            'id' => $kelompok->id,
            'nama' => 'Kelompok Uji Diperbarui',
        ]);

        $this->actingAs($admin)->deleteJson(route('kelompok-urusan.destroy', $kelompok->id))
            ->assertOk();

        $this->assertDatabaseMissing('kelompok_urusan', ['id' => $kelompok->id]);
        $this->assertDatabaseMissing('kelompok_urusan', ['id' => $childKelompok->id]);
        $this->assertDatabaseMissing('urusan', ['id' => $urusan->id]);
        $this->assertDatabaseMissing('indikator', ['id' => $indikator->id]);
        $this->assertDatabaseMissing('data_indikator', ['id' => $data->id]);
    }

    /** @test */
    public function admin_can_create_a_nested_kelompok_from_the_selected_parent()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $staleTestParentIds = KelompokUrusan::where('nama', 'Folder Induk Uji')->pluck('id');
        $staleTestChildIds = KelompokUrusan::whereIn('parent_id', $staleTestParentIds)->pluck('id');
        DB::table('kelompok_urusan')->whereIn('parent_id', $staleTestChildIds)->delete();
        DB::table('kelompok_urusan')->whereIn('id', $staleTestChildIds)->delete();
        DB::table('kelompok_urusan')->whereIn('id', $staleTestParentIds)->delete();

        $parent = KelompokUrusan::create([
            'kode' => 'P' . substr(uniqid(), -5),
            'nama' => 'Folder Induk Uji',
            'urutan' => 999,
        ]);
        $childName = 'Folder Anak ' . uniqid();

        try {
            $response = $this->actingAs($admin)->post(route('kelompok-urusan.store'), [
                'parent_id' => $parent->id,
                'nama' => $childName,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('kelompok_urusan', [
                'parent_id' => $parent->id,
                'nama' => $childName,
            ]);

            $dashboard = $this->actingAs($admin)->get(route('dashboard'));
            $dashboard->assertOk()
                ->assertSee($childName)
                ->assertSee('Nama folder baru...')
                ->assertSee('id="inlineKelompokRootFormRow"', false)
                ->assertSee('id="inlineKelompokRootInput"', false)
                ->assertSee('name="parent_id" value=""', false)
                ->assertSee('name="parent_id" value="' . $parent->id . '"', false);
        } finally {
            $childIds = KelompokUrusan::where('parent_id', $parent->id)->pluck('id');
            DB::table('kelompok_urusan')->whereIn('parent_id', $childIds)->delete();
            DB::table('kelompok_urusan')->whereIn('id', $childIds)->delete();
            $parent->delete();
        }
    }

    /** @test */
    public function urusan_page_shows_hierarchy_and_supports_subindicators()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $sekolah = Urusan::where('nama_urusan', 'Jumlah Sekolah')->first() ?? Urusan::first();

        $response = $this->actingAs($admin)->get('/urusan/' . $sekolah->id);
        $response->assertStatus(200);
        $response->assertSee('Pendidikan');
        $response->assertSee('Jumlah Sekolah');
        $response->assertSee('Sekolah Dasar Negeri');

        // Test adding new subindicator to existing indicator
        $parent = Indikator::where('nama_indikator', 'Sekolah Negeri')->first() ?? Indikator::first();
        $responsePost = $this->actingAs($admin)->post('/indikator', [
            'urusan_id' => $sekolah->id,
            'parent_id' => $parent->id,
            'kode_indikator' => 'TEST-01',
            'nama_indikator' => 'Subindikator Unit Pengujian',
            'satuan' => 'Unit',
            'tipe_data' => 'numerik',
        ]);

        $responsePost->assertRedirect();
        $this->assertDatabaseHas('indikator', [
            'nama_indikator' => 'Subindikator Unit Pengujian',
            'parent_id' => $parent->id,
        ]);
    }

    /** @test */
    public function annual_data_can_be_stored_and_retrieved_per_year()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $ind = Indikator::first();
        $t2024 = Tahun::where('tahun', 2024)->first();
        $t2025 = Tahun::where('tahun', 2025)->first();

        // Save value for 2024
        $this->actingAs($admin)->post('/data/single', [
            'indikator_id' => $ind->id,
            'tahun_id' => $t2024->id,
            'nilai' => '120',
            'sumber_data' => 'Dinas Terkait',
        ]);

        // Save different value for 2025
        $this->actingAs($admin)->post('/data/single', [
            'indikator_id' => $ind->id,
            'tahun_id' => $t2025->id,
            'nilai' => '150',
            'sumber_data' => 'Dinas Terkait',
        ]);

        $this->assertDatabaseHas('data_indikator', [
            'indikator_id' => $ind->id,
            'tahun_id' => $t2024->id,
            'nilai' => '120',
        ]);

        $this->assertDatabaseHas('data_indikator', [
            'indikator_id' => $ind->id,
            'tahun_id' => $t2025->id,
            'nilai' => '150',
        ]);
    }

    /** @test */
    public function cannot_delete_indicator_with_children()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $parent = Indikator::where('nama_indikator', 'Sekolah Negeri')->first() ?? Indikator::has('children')->first();

        $response = $this->actingAs($admin)->delete('/indikator/' . $parent->id);
        $response->assertSessionHas('error');
        // Indicator must still exist
        $this->assertDatabaseHas('indikator', ['id' => $parent->id, 'deleted_at' => null]);
    }


    /** @test */
    public function urusan_pdf_download_works()
    {
        $admin = User::where('email', 'admin@datasektoral.go.id')->first();
        $urusan = Urusan::first();

        $resPdf = $this->actingAs($admin)->get('/urusan/' . ($urusan->slug ?: $urusan->id) . '/pdf?tahun=2025');
        $resPdf->assertStatus(200);
        $this->assertTrue(str_contains($resPdf->headers->get('content-type'), 'pdf'));
    }
}
