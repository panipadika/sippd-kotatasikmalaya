<?php

namespace Tests\Feature;

use App\Models\DataIndikator;
use App\Models\Indikator;
use App\Models\KelompokUrusan;
use App\Models\Tahun;
use App\Models\Urusan;
use App\Models\User;
use App\Services\ExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubColumnFooterTotalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $kelompok;
    protected $urusan;
    protected $tahun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_test',
        ]);

        $this->kelompok = KelompokUrusan::create([
            'nama' => 'Pertanian & Perikanan',
            'kode' => '03',
            'urutan' => 1,
        ]);

        $this->tahun = Tahun::create([
            'tahun' => 2024,
            'status_aktif' => true,
            'is_default' => true,
        ]);

        // Urusan dengan kolom ber-subkolom (seperti Kolam dan Sawah di bawah 2024)
        $this->urusan = Urusan::create([
            'kelompok_urusan_id' => $this->kelompok->id,
            'nama_urusan' => 'Jumlah RTP Perikanan',
            'slug' => 'jumlah-rtp-perikanan',
            'status_aktif' => true,
            'table_configs' => [
                [
                    'id' => 1,
                    'name' => 'Tabel 1',
                    'show_footer_total' => true,
                    'footer_label' => 'Jumlah',
                    'footer_subtotal_label' => 'Total',
                    'show_footer_subtotal' => true,
                    'columns' => [
                        [
                            'id' => 'col_no',
                            'name' => 'No',
                            'type' => 'text',
                        ],
                        [
                            'id' => 'col_kecamatan',
                            'name' => 'Kecamatan',
                            'type' => 'text',
                        ],
                        [
                            'id' => 'col_rtp',
                            'name' => 'Jumlah RTP',
                            'type' => 'text',
                            'sub_columns' => [
                                [
                                    'id' => 'col_2024',
                                    'name' => '2024',
                                    'type' => 'number',
                                    'is_total' => false,
                                    'sub_columns' => [
                                        [
                                            'id' => 'col_2024_kolam',
                                            'name' => 'Kolam',
                                            'type' => 'number',
                                            'is_total' => false,
                                        ],
                                        [
                                            'id' => 'col_2024_sawah',
                                            'name' => 'Sawah',
                                            'type' => 'number',
                                            'is_total' => false,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_table_footer_renders_subtotal_row_combining_subcolumns()
    {
        $ind = Indikator::create([
            'urusan_id' => $this->urusan->id,
            'table_id' => 1,
            'nama_indikator' => 'Mangkubumi',
            'tipe_data' => 'numerik',
            'urutan' => 1,
            'status_aktif' => true,
        ]);

        DataIndikator::create([
            'indikator_id' => $ind->id,
            'tahun_id' => $this->tahun->id,
            'custom_fields' => [
                'col_2024_kolam' => '24',
                'col_2024_sawah' => '54',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('urusan.show', $this->urusan->slug));
        $response->assertStatus(200);

        // Verifikasi Leaf Totals di Baris 1: Kolam = 24, Sawah = 54
        $response->assertSee('24');
        $response->assertSee('54');

        // Verifikasi Baris 2 (footer-subtotal-row) dan Total Gabungan: 24 + 54 = 78
        $response->assertSee('footer-subtotal-row');
        $response->assertSee('footer-subtotal-cell');
        $response->assertSee('colspan="2"', false);
        $response->assertSee('78');
        $response->assertSee('Total gabungan sub-kolom (Kolam + Sawah) untuk 2024');
    }

    public function test_can_save_footer_override_for_subtotal_and_custom_labels()
    {
        // Ubah label baris 1 jadi "Kota" dan baris 2 jadi "Tasikmalaya"
        $response = $this->actingAs($this->admin)->post(route('urusan.columns', $this->urusan->slug), [
            'action' => 'set_footer_override',
            'table_id' => 1,
            'footer_label' => 'Kota',
            'footer_subtotal_label' => 'Tasikmalaya',
            'col_id' => 'subtotal_col_2024',
            'value' => '12194',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $this->urusan->refresh();
        $table = $this->urusan->getTableConfig(1);

        $this->assertEquals('Kota', $table['footer_label']);
        $this->assertEquals('Tasikmalaya', $table['footer_subtotal_label']);
        $this->assertEquals('12194', $table['footer_overrides']['subtotal_col_2024']);
    }

    public function test_excel_and_pdf_export_support_footer_subtotal_row()
    {
        $service = new ExcelService();
        $excelResponse = $service->export($this->urusan, 1, 2024, 2024);
        $this->assertEquals(200, $excelResponse->getStatusCode());

        $pdfResponse = $this->actingAs($this->admin)->get(route('urusan.pdf', [
            'id' => $this->urusan->slug,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2024,
        ]));
        $this->assertEquals(200, $pdfResponse->getStatusCode());
    }
}
