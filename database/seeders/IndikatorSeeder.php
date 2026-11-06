<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\Tahun;
use App\Models\DataIndikator;
use App\Models\User;

class IndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $uSekolah = Urusan::where('slug', 'jumlah-sekolah')->first();
        $uGuru = Urusan::where('slug', 'jumlah-guru')->first();
        $uSiswa = Urusan::where('slug', 'jumlah-siswa')->first();
        $uRuang = Urusan::where('slug', 'jumlah-ruang-kelas')->first();
        $uKes = Urusan::where('slug', 'jumlah-fasilitas-pelayanan-kesehatan')->first();

        $t2023 = Tahun::firstOrCreate(['tahun' => 2023], ['status_aktif' => true, 'is_default' => false]);
        $t2024 = Tahun::firstOrCreate(['tahun' => 2024], ['status_aktif' => true, 'is_default' => false]);
        $t2025 = Tahun::firstOrCreate(['tahun' => 2025], ['status_aktif' => true, 'is_default' => true]);

        if ($uSekolah) {
            $indNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Negeri', 'parent_id' => null],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SEK-01']
            );
            $sdNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Dasar Negeri', 'parent_id' => $indNegeri->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SEK-01.1']
            );
            $smpNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Menengah Pertama Negeri', 'parent_id' => $indNegeri->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SEK-01.2']
            );

            $indSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Swasta', 'parent_id' => null],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SEK-02']
            );
            $sdSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Dasar Swasta', 'parent_id' => $indSwasta->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SEK-02.1']
            );
            $smpSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uSekolah->id, 'nama_indikator' => 'Sekolah Menengah Pertama Swasta', 'parent_id' => $indSwasta->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SEK-02.2']
            );

            $samplesSekolah = [
                ['ind' => $sdNegeri, 'th' => $t2023, 'val' => '75'],
                ['ind' => $sdNegeri, 'th' => $t2024, 'val' => '78'],
                ['ind' => $sdNegeri, 'th' => $t2025, 'val' => '80'],
                ['ind' => $smpNegeri, 'th' => $t2023, 'val' => '18'],
                ['ind' => $smpNegeri, 'th' => $t2024, 'val' => '19'],
                ['ind' => $smpNegeri, 'th' => $t2025, 'val' => '20'],
                ['ind' => $sdSwasta, 'th' => $t2023, 'val' => '32'],
                ['ind' => $sdSwasta, 'th' => $t2024, 'val' => '35'],
                ['ind' => $sdSwasta, 'th' => $t2025, 'val' => '38'],
                ['ind' => $smpSwasta, 'th' => $t2024, 'val' => '14'],
                ['ind' => $smpSwasta, 'th' => $t2025, 'val' => '15'],
            ];
            foreach ($samplesSekolah as $s) {
                DataIndikator::updateOrCreate(
                    ['indikator_id' => $s['ind']->id, 'tahun_id' => $s['th']->id],
                    ['nilai' => $s['val'], 'sumber_data' => 'Dinas Pendidikan', 'updated_by' => $admin ? $admin->id : null]
                );
            }
        }

        if ($uGuru) {
            $guruNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uGuru->id, 'nama_indikator' => 'Guru Negeri', 'parent_id' => null],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'GURU-01']
            );
            $guruSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uGuru->id, 'nama_indikator' => 'Guru Swasta', 'parent_id' => null],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'GURU-02']
            );

            $samplesGuru = [
                ['ind' => $guruNegeri, 'th' => $t2024, 'val' => '1250'],
                ['ind' => $guruNegeri, 'th' => $t2025, 'val' => '1320'],
                ['ind' => $guruSwasta, 'th' => $t2024, 'val' => '640'],
                ['ind' => $guruSwasta, 'th' => $t2025, 'val' => '675'],
            ];
            foreach ($samplesGuru as $s) {
                DataIndikator::updateOrCreate(
                    ['indikator_id' => $s['ind']->id, 'tahun_id' => $s['th']->id],
                    ['nilai' => $s['val'], 'sumber_data' => 'Dinas Pendidikan', 'updated_by' => $admin ? $admin->id : null]
                );
            }
        }

        if ($uSiswa) {
            $siswaSd = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa Sekolah Dasar (SD)', 'parent_id' => null],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SIS-01']
            );
            $siswaSdNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa SD Negeri', 'parent_id' => $siswaSd->id],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SIS-01.1']
            );
            $siswaSdSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa SD Swasta', 'parent_id' => $siswaSd->id],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SIS-01.2']
            );
            $siswaSmp = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa Sekolah Menengah Pertama (SMP)', 'parent_id' => null],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SIS-02']
            );
            $siswaSmpNegeri = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa SMP Negeri', 'parent_id' => $siswaSmp->id],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'SIS-02.1']
            );
            $siswaSmpSwasta = Indikator::updateOrCreate(
                ['urusan_id' => $uSiswa->id, 'nama_indikator' => 'Siswa SMP Swasta', 'parent_id' => $siswaSmp->id],
                ['satuan' => 'Orang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'SIS-02.2']
            );

            $samplesSiswa = [
                ['ind' => $siswaSdNegeri, 'th' => $t2024, 'val' => '15400'],
                ['ind' => $siswaSdNegeri, 'th' => $t2025, 'val' => '15850'],
                ['ind' => $siswaSdSwasta, 'th' => $t2024, 'val' => '4200'],
                ['ind' => $siswaSdSwasta, 'th' => $t2025, 'val' => '4350'],
                ['ind' => $siswaSmpNegeri, 'th' => $t2024, 'val' => '8900'],
                ['ind' => $siswaSmpNegeri, 'th' => $t2025, 'val' => '9120'],
                ['ind' => $siswaSmpSwasta, 'th' => $t2024, 'val' => '2450'],
                ['ind' => $siswaSmpSwasta, 'th' => $t2025, 'val' => '2500'],
            ];
            foreach ($samplesSiswa as $s) {
                DataIndikator::updateOrCreate(
                    ['indikator_id' => $s['ind']->id, 'tahun_id' => $s['th']->id],
                    ['nilai' => $s['val'], 'sumber_data' => 'Dinas Pendidikan', 'updated_by' => $admin ? $admin->id : null]
                );
            }
        }

        if ($uRuang) {
            $rkSd = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Ruang Kelas SD Negeri', 'parent_id' => null],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'RK-01']
            );
            $rkSdBaik = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Kondisi Baik', 'parent_id' => $rkSd->id],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'RK-01.1']
            );
            $rkSdRusak = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Kondisi Rusak Ringan/Sedang', 'parent_id' => $rkSd->id],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'RK-01.2']
            );
            $rkSmp = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Ruang Kelas SMP Negeri', 'parent_id' => null],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'RK-02']
            );
            $rkSmpBaik = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Kondisi Baik', 'parent_id' => $rkSmp->id],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'RK-02.1']
            );
            $rkSmpRusak = Indikator::updateOrCreate(
                ['urusan_id' => $uRuang->id, 'nama_indikator' => 'Kondisi Rusak Ringan/Sedang', 'parent_id' => $rkSmp->id],
                ['satuan' => 'Ruang', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'RK-02.2']
            );

            $samplesRuang = [
                ['ind' => $rkSdBaik, 'th' => $t2024, 'val' => '320'],
                ['ind' => $rkSdBaik, 'th' => $t2025, 'val' => '335'],
                ['ind' => $rkSdRusak, 'th' => $t2024, 'val' => '45'],
                ['ind' => $rkSdRusak, 'th' => $t2025, 'val' => '38'],
            ];
            foreach ($samplesRuang as $s) {
                DataIndikator::updateOrCreate(
                    ['indikator_id' => $s['ind']->id, 'tahun_id' => $s['th']->id],
                    ['nilai' => $s['val'], 'sumber_data' => 'Dinas Pendidikan', 'updated_by' => $admin ? $admin->id : null]
                );
            }
        }

        if ($uKes) {
            $faskesRs = Indikator::updateOrCreate(
                ['urusan_id' => $uKes->id, 'nama_indikator' => 'Rumah Sakit Umum Daerah', 'parent_id' => null],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'KES-01']
            );
            $faskesPusk = Indikator::updateOrCreate(
                ['urusan_id' => $uKes->id, 'nama_indikator' => 'Puskesmas', 'parent_id' => null],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'KES-02']
            );
            $faskesInap = Indikator::updateOrCreate(
                ['urusan_id' => $uKes->id, 'nama_indikator' => 'Puskesmas Rawat Inap', 'parent_id' => $faskesPusk->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 1, 'kode_indikator' => 'KES-02.1']
            );
            $faskesNonInap = Indikator::updateOrCreate(
                ['urusan_id' => $uKes->id, 'nama_indikator' => 'Puskesmas Non Rawat Inap', 'parent_id' => $faskesPusk->id],
                ['satuan' => 'Unit', 'tipe_data' => 'numerik', 'urutan' => 2, 'kode_indikator' => 'KES-02.2']
            );

            $samplesKes = [
                ['ind' => $faskesRs, 'th' => $t2024, 'val' => '2'],
                ['ind' => $faskesRs, 'th' => $t2025, 'val' => '2'],
                ['ind' => $faskesInap, 'th' => $t2024, 'val' => '8'],
                ['ind' => $faskesInap, 'th' => $t2025, 'val' => '9'],
                ['ind' => $faskesNonInap, 'th' => $t2024, 'val' => '14'],
                ['ind' => $faskesNonInap, 'th' => $t2025, 'val' => '14'],
            ];
            foreach ($samplesKes as $s) {
                DataIndikator::updateOrCreate(
                    ['indikator_id' => $s['ind']->id, 'tahun_id' => $s['th']->id],
                    ['nilai' => $s['val'], 'sumber_data' => 'Dinas Kesehatan', 'updated_by' => $admin ? $admin->id : null]
                );
            }
        }
    }
}
