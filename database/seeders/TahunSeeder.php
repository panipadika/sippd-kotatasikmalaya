<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tahun;

class TahunSeeder extends Seeder
{
    public function run(): void
    {
        $tahuns = [
            ['tahun' => 2023, 'status_aktif' => true, 'is_default' => false, 'keterangan' => 'Tahun Anggaran 2023'],
            ['tahun' => 2024, 'status_aktif' => true, 'is_default' => false, 'keterangan' => 'Tahun Anggaran 2024'],
            ['tahun' => 2025, 'status_aktif' => true, 'is_default' => true, 'keterangan' => 'Tahun Anggaran 2025 (Aktif)'],
        ];

        foreach ($tahuns as $t) {
            Tahun::updateOrCreate(
                ['tahun' => $t['tahun']],
                $t
            );
        }
    }
}
