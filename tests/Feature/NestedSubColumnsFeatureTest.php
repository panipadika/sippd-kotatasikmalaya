<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\DataIndikator;
use App\Models\Tahun;
use App\Services\ExcelService;

class NestedSubColumnsFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_nested_sub_columns_in_sub_columns_and_render_3_level_header(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        if (!$superAdmin) {
            $superAdmin = User::factory()->create([
                'username' => 'superadmin_test',
                'role' => 'super_admin',
            ]);
        }

        // Create or find a test Urusan
        $urusan = Urusan::first();
        $this->assertNotNull($urusan);

        $tables = $urusan->tables;
        $activeTableId = 1;

        // Setup base columns matching the user's scenario:
        // Col 1: Kecamatan
        // Col 2: Jumlah RTP (with sub 2024, 2025)
        // Col 3: Produksi (Ton) (with sub 2024, 2025)
        $cols = [
            [
                'id' => 'col_kecamatan',
                'name' => 'Kecamatan',
                'type' => 'text',
            ],
            [
                'id' => 'col_rtp',
                'name' => 'Jumlah RTP',
                'type' => 'number',
                'sub_columns' => [
                    [
                        'id' => 'col_rtp_sub_1',
                        'name' => '2024',
                        'type' => 'number',
                    ],
                    [
                        'id' => 'col_rtp_sub_2',
                        'name' => '2025',
                        'type' => 'number',
                    ],
                ],
            ],
            [
                'id' => 'col_produksi',
                'name' => 'Produksi (Ton)',
                'type' => 'number',
                'sub_columns' => [
                    [
                        'id' => 'col_prod_sub_1',
                        'name' => '2024',
                        'type' => 'number',
                    ],
                    [
                        'id' => 'col_prod_sub_2',
                        'name' => '2025',
                        'type' => 'number',
                    ],
                ],
            ],
        ];

        // 1. Save base table structure
        $tables[0]['columns'] = $cols;
        $urusan->update([
            'table_configs' => $tables,
            'custom_columns' => $cols,
        ]);

        // 2. Add nested sub-columns (Kolam, Sawah) inside col_rtp_sub_1 (2024)
        // with apply_to_all_years checked
        $res = $this->actingAs($superAdmin)->post(route('urusan.columns', $urusan->slug ?: $urusan->id), [
            'action' => 'set_sub_columns',
            'table_id' => $activeTableId,
            'parent_col_id' => 'col_rtp_sub_1',
            'sub_names' => ['Kolam', 'Sawah'],
            'apply_to_all_years' => '1',
        ]);
        $res->assertSessionHasNoErrors();

        // Reload Urusan
        $urusan->refresh();
        $activeCols = $urusan->getColumnsForTable($activeTableId);

        // Find 2024 under col_rtp
        $rtpCol = collect($activeCols)->firstWhere('id', 'col_rtp');
        $this->assertNotNull($rtpCol);
        $this->assertNotEmpty($rtpCol['sub_columns']);

        $yr2024 = collect($rtpCol['sub_columns'])->firstWhere('id', 'col_rtp_sub_1');
        $this->assertNotNull($yr2024);
        $this->assertNotEmpty($yr2024['sub_columns']);
        $this->assertEquals('Kolam', $yr2024['sub_columns'][0]['name']);
        $this->assertEquals('Sawah', $yr2024['sub_columns'][1]['name']);

        // Since apply_to_all_years was true, 2025 under col_rtp and 2024/2025 under col_produksi
        // should also have Kolam and Sawah!
        $yr2025 = collect($rtpCol['sub_columns'])->firstWhere('id', 'col_rtp_sub_2');
        $this->assertNotNull($yr2025);
        $this->assertNotEmpty($yr2025['sub_columns']);
        $this->assertEquals('Kolam', $yr2025['sub_columns'][0]['name']);

        $prodCol = collect($activeCols)->firstWhere('id', 'col_produksi');
        $this->assertNotNull($prodCol);
        $prod2024 = collect($prodCol['sub_columns'])->firstWhere('id', 'col_prod_sub_1');
        $this->assertNotNull($prod2024);
        $this->assertNotEmpty($prod2024['sub_columns']);
        $this->assertEquals('Kolam', $prod2024['sub_columns'][0]['name']);

        // 3. Render HTML Table and verify 3-level headers
        $viewRes = $this->actingAs($superAdmin)->get(route('urusan.show', $urusan->slug ?: $urusan->id));
        $viewRes->assertStatus(200);
        $html = $viewRes->getContent();

        // Level 1: "Kecamatan" rowspan 3
        $this->assertMatchesRegularExpression('/rowspan="3"[^>]*>\s*<div[^>]*>\s*<span[^>]*>[^<]*Kecamatan/i', $html);

        // Level 1: "Jumlah RTP" colspan 4 (2024 has 2, 2025 has 2 = 4 leaves)
        $this->assertMatchesRegularExpression('/colspan="4"[^>]*rowspan="1"[^>]*>[\s\S]*?Jumlah RTP/i', $html);

        // Level 2: "2024" colspan 2
        $this->assertMatchesRegularExpression('/colspan="2"[^>]*rowspan="1"[^>]*>[\s\S]*?2024/i', $html);

        // Level 3: "Kolam" and "Sawah" headers present
        $this->assertStringContainsString('Kolam', $html);
        $this->assertStringContainsString('Sawah', $html);

        // 4. Test storing data into a nested sub-column leaf
        $leafKolamId = $yr2024['sub_columns'][0]['id'];
        $indikator = Indikator::where('urusan_id', $urusan->id)->first();
        if (!$indikator) {
            $indikator = Indikator::create([
                'urusan_id' => $urusan->id,
                'nama_indikator' => 'Kawalu',
                'urutan' => 1,
                'table_id' => $activeTableId,
            ]);
        }

        $th2024 = Tahun::firstOrCreate(['tahun' => 2024], ['status_aktif' => true, 'is_default' => false]);
        $updateCellRes = $this->actingAs($superAdmin)->post(route('indikator.update-cell', $indikator->id), [
            'field' => $leafKolamId,
            'value' => '1.652',
            'tahun_id' => $th2024->id,
        ]);
        $updateCellRes->assertJson(['success' => true]);

        // Verify stored value
        $di = DataIndikator::where('indikator_id', $indikator->id)->where('tahun_id', $th2024->id)->first();
        $this->assertNotNull($di);
        $this->assertEquals('1.652', $di->getCustomValue($leafKolamId));

        // 5. Verify PDF export does not crash
        $pdfRes = $this->actingAs($superAdmin)->get(route('urusan.pdf', [
            'id' => $urusan->slug ?: $urusan->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2025,
            'tabel' => $activeTableId,
        ]));
        $pdfRes->assertStatus(200);

        // 6. Verify Excel export builds headers properly
        $excelService = app(ExcelService::class);
        $reflector = new \ReflectionClass($excelService);
        $buildHeaderMethod = $reflector->getMethod('buildHeader');
        $buildHeaderMethod->setAccessible(true);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $tahuns = Tahun::all()->keyBy('tahun');

        $headerInfo = $buildHeaderMethod->invoke($excelService, $sheet, $urusan, $activeCols, true, $tahuns, 2024, 2025, false);
        $this->assertNotEmpty($headerInfo['flatCols']);
        // Verify leaf column is in flatCols
        $flatSubIds = array_filter(array_column($headerInfo['flatCols'], 'sub_id'));
        $this->assertContains($leafKolamId, $flatSubIds);

        // 7. Test delete_sub_column on nested leaf
        $delRes = $this->actingAs($superAdmin)->post(route('urusan.columns', $urusan->slug ?: $urusan->id), [
            'action' => 'delete_sub_column',
            'table_id' => $activeTableId,
            'sub_col_id' => $leafKolamId,
        ]);
        $delRes->assertSessionHasNoErrors();

        $urusan->refresh();
        $afterCols = $urusan->getColumnsForTable($activeTableId);
        $afterRtp = collect($afterCols)->firstWhere('id', 'col_rtp');
        $after2024 = collect($afterRtp['sub_columns'])->firstWhere('id', 'col_rtp_sub_1');
        $remainingSubIds = array_column($after2024['sub_columns'] ?? [], 'id');
        $this->assertNotContains($leafKolamId, $remainingSubIds);
    }
}
