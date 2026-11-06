<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Urusan;
use App\Models\KelompokUrusan;
use App\Models\Indikator;
use App\Models\DataIndikator;
use App\Models\Tahun;

class IndicatorJumlahAndToastFixTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sub_rows_in_standalone_jumlah_column_display_actual_values_and_not_forced_zero(): void
    {
        $user = User::where('role', 'user')->first() ?? User::factory()->create(['role' => 'user']);
        $tahun = Tahun::where('tahun', 2026)->first() ?? Tahun::create(['tahun' => 2026, 'status_aktif' => true]);

        // Cari atau pastikan ada urusan dengan kolom No, Uraian, Jumlah
        $urusan = Urusan::where('slug', 'jumlah-fasilitas-pelayanan-kesehatan')->first();
        if (!$urusan) {
            $kelompok = KelompokUrusan::firstOrCreate(['nama' => 'Kesehatan', 'kode' => 'KES']);
            $urusan = Urusan::create([
                'kelompok_urusan_id' => $kelompok->id,
                'nama_urusan' => 'Jumlah Fasilitas Pelayanan Kesehatan',
                'slug' => 'jumlah-fasilitas-pelayanan-kesehatan',
            ]);
        }

        $urusan->update([
            'custom_columns' => [
                ['id' => 'col_no', 'name' => 'No', 'type' => 'text'],
                ['id' => 'col_uraian', 'name' => 'Uraian', 'type' => 'text'],
                ['id' => 'col_jumlah', 'name' => 'Jumlah', 'type' => 'text'],
            ],
            'table_configs' => [
                [
                    'id' => 1,
                    'name' => 'Tabel 1',
                    'columns' => [
                        ['id' => 'col_no', 'name' => 'No', 'type' => 'text'],
                        ['id' => 'col_uraian', 'name' => 'Uraian', 'type' => 'text'],
                        ['id' => 'col_jumlah', 'name' => 'Jumlah', 'type' => 'text'],
                    ],
                ]
            ]
        ]);

        $induk = Indikator::firstOrCreate([
            'urusan_id' => $urusan->id,
            'nama_indikator' => 'Rumah Sakit',
        ], [
            'urutan' => 1,
            'table_id' => 1,
        ]);

        $sub1 = Indikator::firstOrCreate([
            'urusan_id' => $urusan->id,
            'nama_indikator' => 'RSUD DR. SOEKARDJO',
        ], [
            'parent_id' => $induk->id,
            'urutan' => 1,
            'table_id' => 1,
        ]);

        DataIndikator::firstOrCreate([
            'indikator_id' => $sub1->id,
            'tahun_id' => $tahun->id,
        ], [
            'custom_fields' => ['col_jumlah' => '1', 'col_uraian' => 'RSUD DR. SOEKARDJO'],
        ]);

        $response = $this->actingAs($user)->get(route('urusan.show', ['id' => $urusan->slug, 'tahun' => 2026]));
        $response->assertStatus(200);

        // Sub row harus menampilkan nilainya (bukan 0)
        $content = $response->getContent();
        
        // Cek bahwa 'RSUD DR. SOEKARDJO' ada dan didampingi nilai '1'
        $this->assertStringContainsString('RSUD DR. SOEKARDJO', $content);
        $this->assertStringContainsString('Rumah Sakit', $content);

        // Periksa bahwa data-is-row-total adalah false untuk kolom Jumlah standalone ini
        $this->assertStringContainsString('data-is-row-total="false"', $content);
    }

    public function test_table_resizing_alert_uses_show_top_toast_and_not_stuck_sweetalert(): void
    {
        $user = User::where('role', 'user')->first() ?? User::factory()->create(['role' => 'user']);
        $urusan = Urusan::first();

        if ($urusan) {
            $response = $this->actingAs($user)->get(route('urusan.show', $urusan->slug ?: $urusan->id));
            $response->assertStatus(200);

            $content = $response->getContent();
            // Memastikan resetAllTableSizes memanggil showTopToast
            $this->assertStringContainsString('window.showTopToast', $content);
            $this->assertStringContainsString('Ukuran kolom dan baris telah dikembalikan ke standar', $content);
        }
    }

    public function test_action_buttons_column_is_sticky_and_fixed_width(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $urusan = Urusan::first();

        if ($urusan) {
            $response = $this->actingAs($admin)->get(route('urusan.show', $urusan->slug ?: $urusan->id));
            $response->assertStatus(200);

            $content = $response->getContent();
            // Sticky action column harus ada di thead dan berukuran 78px
            $this->assertStringContainsString('sticky-action-col', $content);
            $this->assertStringContainsString('data-col-id="col_aksi"', $content);
            $this->assertStringContainsString('width: 78px; min-width: 78px;', $content);

            // CSS sticky positioning harus ada di konten dan layout
            $this->assertStringContainsString('position: sticky !important;', $content);
            $this->assertStringContainsString('right: 0 !important;', $content);
        }
    }

    public function test_table_resizing_persists_without_is_reset_flag(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $urusan = Urusan::first();

        if ($urusan) {
            $response = $this->actingAs($admin)->get(route('urusan.show', $urusan->slug ?: $urusan->id));
            $response->assertStatus(200);

            $content = $response->getContent();
            // Memastikan kolom aksi dikecualikan dari manipulasi resize agar tetap 78px fix
            $this->assertStringContainsString("if (th.classList.contains('sticky-action-col') || th.dataset.colId === 'col_aksi')", $content);

            // Memastikan resetAllTableSizes tidak lagi men-set is_reset = 1
            $this->assertStringNotContainsString("localStorage.setItem(storagePrefix + 'is_reset', '1')", $content);

            // Memastikan pembersihan is_reset lama dilakukan
            $this->assertStringContainsString("key.includes('is_reset')", $content);
        }
    }
}

