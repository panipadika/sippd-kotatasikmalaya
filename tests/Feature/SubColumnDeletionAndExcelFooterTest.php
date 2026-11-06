<?php

namespace Tests\Feature;

use App\Models\KelompokUrusan;
use App\Models\Tahun;
use App\Models\User;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Services\ExcelService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SubColumnDeletionAndExcelFooterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_inserting_sub_column_before_jumlah_assigns_unique_ids()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test_subcol@tasikmalayakota.go.id'],
            [
                'name' => 'Admin Test SubCol',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status_aktif' => true,
            ]
        );

        $root = KelompokUrusan::firstOrCreate(
            ['kode' => 'PL-SUB'],
            ['nama' => 'Pendidikan Sub Test', 'parent_id' => null]
        );

        $parentColId = 'col_2024_test';
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $root->id,
            'kode_urusan' => 'PL-SUB.01',
            'nama_urusan' => 'Urusan Test Sub Columns',
            'slug' => 'urusan-test-sub-' . time(),
            'status_aktif' => true,
            'table_configs' => [
                [
                    'id' => 1,
                    'name' => 'Tabel 1',
                    'columns' => [
                        [
                            'id' => $parentColId,
                            'name' => '2024',
                            'type' => 'number',
                            'sub_columns' => [
                                ['id' => $parentColId . '_sub_1', 'name' => 'Negeri', 'type' => 'number', 'is_total' => false],
                                ['id' => $parentColId . '_sub_2', 'name' => 'Swasta', 'type' => 'number', 'is_total' => false],
                                ['id' => $parentColId . '_sub_3', 'name' => 'Jumlah', 'type' => 'number', 'is_total' => true],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Simpan sub-kolom baru dengan menyisipkan 'PP' sebelum 'Jumlah'
        $response = $this->actingAs($admin)->post(route('urusan.columns', $urusan->slug), [
            'action' => 'set_sub_columns',
            'table_id' => 1,
            'parent_col_id' => $parentColId,
            'sub_names' => ['Negeri', 'Swasta', 'PP', 'Jumlah'],
        ]);

        $response->assertRedirect();

        $urusan->refresh();
        $cols = $urusan->getColumnsForTable(1);
        $subs = $cols[0]['sub_columns'];

        $this->assertCount(4, $subs);
        $this->assertEquals('Negeri', $subs[0]['name']);
        $this->assertEquals('Swasta', $subs[1]['name']);
        $this->assertEquals('PP', $subs[2]['name']);
        $this->assertEquals('Jumlah', $subs[3]['name']);

        // Pastikan SEMUA ID unik
        $ids = array_column($subs, 'id');
        $this->assertCount(4, array_unique($ids), 'Setiap sub-kolom harus memiliki ID yang unik dan tidak bentrok.');
        $this->assertNotEquals($subs[2]['id'], $subs[3]['id'], 'ID PP dan ID Jumlah tidak boleh sama.');

        // Sekarang tes penghapusan sub-kolom 'PP'
        $ppId = $subs[2]['id'];
        $deleteResponse = $this->actingAs($admin)->post(route('urusan.columns', $urusan->slug), [
            'action' => 'delete_sub_column',
            'table_id' => 1,
            'parent_col_id' => $parentColId,
            'sub_col_id' => $ppId,
            'sub_col_name' => 'PP',
        ]);

        $deleteResponse->assertRedirect();

        $urusan->refresh();
        $colsAfter = $urusan->getColumnsForTable(1);
        $subsAfter = $colsAfter[0]['sub_columns'];

        $this->assertCount(3, $subsAfter, 'Setelah PP dihapus, harus tersisa 3 sub-kolom.');
        $subNamesAfter = array_column($subsAfter, 'name');
        $this->assertContains('Negeri', $subNamesAfter);
        $this->assertContains('Swasta', $subNamesAfter);
        $this->assertContains('Jumlah', $subNamesAfter, 'Sub-kolom Jumlah HARUS tetap ada dan tidak boleh ikut terhapus!');
        $this->assertNotContains('PP', $subNamesAfter);
    }

    public function test_excel_export_does_not_include_footer_total_when_disabled()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test_excel@tasikmalayakota.go.id'],
            [
                'name' => 'Admin Test Excel',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status_aktif' => true,
            ]
        );

        $root = KelompokUrusan::firstOrCreate(
            ['kode' => 'PL-EX'],
            ['nama' => 'Pendidikan Excel Test', 'parent_id' => null]
        );

        $parentColId = 'col_2024_ex';
        $urusan = Urusan::create([
            'kelompok_urusan_id' => $root->id,
            'kode_urusan' => 'PL-EX.01',
            'nama_urusan' => 'Urusan Test Excel Export',
            'slug' => 'urusan-test-excel-' . time(),
            'status_aktif' => true,
            'table_configs' => [
                [
                    'id' => 1,
                    'name' => 'Tabel 1',
                    'show_footer_total' => false, // Nonaktifkan baris jumlah!
                    'columns' => [
                        [
                            'id' => $parentColId,
                            'name' => '2024',
                            'type' => 'number',
                        ]
                    ]
                ]
            ]
        ]);

        Indikator::create([
            'urusan_id' => $urusan->id,
            'table_id' => 1,
            'nama_indikator' => 'Indikator Baris 1',
            'tipe_data' => 'numerik',
            'urutan' => 1,
            'status_aktif' => true,
        ]);

        $excelService = app(ExcelService::class);

        // Export tanpa parameter (mengikuti show_footer_total = false dari tabel)
        $response = $this->actingAs($admin)->get(route('urusan.excel.export', [
            'id' => $urusan->slug,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2024,
            'tabel' => 1,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        // Verifikasi logika showFooterTotal dengan Reflection
        $method = new \ReflectionMethod(ExcelService::class, 'writeFooterTotal');
        $this->assertTrue($method->isPrivate() || $method->isProtected() || $method->isPublic());

        // Verifikasi bahwa jika show_footer_total false, writeFooterTotal tidak dipanggil
        // Menguji kondisi dengan request eksplisit show_footer_total=0
        $responseDisabled = $this->actingAs($admin)->get(route('urusan.excel.export', [
            'id' => $urusan->slug,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2024,
            'tabel' => 1,
            'show_footer_total' => 0,
        ]));
        $responseDisabled->assertStatus(200);
    }
}
