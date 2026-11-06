<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelompokUrusan;
use App\Models\Urusan;
use Illuminate\Support\Str;

class UrusanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Root Groups (A through H)
        $rootGroups = [
            ['kode' => 'A', 'nama' => 'Urusan Wajib Pelayanan Dasar', 'urutan' => 1],
            ['kode' => 'B', 'nama' => 'Urusan Wajib Nonpelayanan Dasar', 'urutan' => 2],
            ['kode' => 'C', 'nama' => 'Urusan Pilihan', 'urutan' => 3],
            ['kode' => 'D', 'nama' => 'Pendukung Urusan Pemerintahan', 'urutan' => 4],
            ['kode' => 'E', 'nama' => 'Penunjang Urusan Pemerintahan', 'urutan' => 5],
            ['kode' => 'F', 'nama' => 'Pengawasan Urusan Pemerintahan', 'urutan' => 6],
            ['kode' => 'G', 'nama' => 'Urusan Pemerintahan Umum Kewilayahan', 'urutan' => 7],
            ['kode' => 'H', 'nama' => 'Urusan Pemerintahan Umum', 'urutan' => 8],
        ];

        $rootMap = [];
        foreach ($rootGroups as $rg) {
            $root = KelompokUrusan::updateOrCreate(
                ['kode' => $rg['kode'], 'parent_id' => null],
                ['nama' => $rg['nama'], 'urutan' => $rg['urutan']]
            );
            $rootMap[$rg['kode']] = $root;
        }

        // 2. Child Folders (38 Urusan Pemerintahan / Bidang)
        $subFolders = [
            'A' => [
                ['nama' => 'Pendidikan', 'urutan' => 1],
                ['nama' => 'Kesehatan', 'urutan' => 2],
                ['nama' => 'Pekerjaan umum dan penataan ruang', 'urutan' => 3],
                ['nama' => 'Perumahan rakyat dan kawasan pemukiman', 'urutan' => 4],
                ['nama' => 'Ketentraman, ketertiban umum danperlindungan...', 'urutan' => 5],
                ['nama' => 'Sosial', 'urutan' => 6],
            ],
            'B' => [
                ['nama' => 'Tenaga Kerja', 'urutan' => 1],
                ['nama' => 'Pemberdayaan Perempuan dan Perlindungan Anak', 'urutan' => 2],
                ['nama' => 'Pangan', 'urutan' => 3],
                ['nama' => 'Pertanahan', 'urutan' => 4],
                ['nama' => 'Lingkungan Hidup', 'urutan' => 5],
                ['nama' => 'Administrasi Kependudukan dan Pencatatan Sipil', 'urutan' => 6],
                ['nama' => 'Pengendalian Penduduk dan Keluarga Berencana', 'urutan' => 7],
                ['nama' => 'Perhubungan', 'urutan' => 8],
                ['nama' => 'Komunikasi dan Informatika', 'urutan' => 9],
                ['nama' => 'Koperasi, Usaha Kecil dan Menengah', 'urutan' => 10],
                ['nama' => 'Penanaman Modal', 'urutan' => 11],
                ['nama' => 'Kepemudaan dan Olahraga', 'urutan' => 12],
                ['nama' => 'Statistik', 'urutan' => 13],
                ['nama' => 'Persandian', 'urutan' => 14],
                ['nama' => 'Kebudayaan', 'urutan' => 15],
                ['nama' => 'Perpustakaan', 'urutan' => 16],
                ['nama' => 'Kearsipan', 'urutan' => 17],
            ],
            'C' => [
                ['nama' => 'Perikanan', 'urutan' => 1],
                ['nama' => 'Pariwisata', 'urutan' => 2],
                ['nama' => 'Pertanian', 'urutan' => 3],
                ['nama' => 'Perdagangan', 'urutan' => 4],
                ['nama' => 'Perindustrian', 'urutan' => 5],
            ],
            'D' => [
                ['nama' => 'Sekretariat Daerah', 'urutan' => 1],
                ['nama' => 'Sekretariat DPRD', 'urutan' => 2],
            ],
            'E' => [
                ['nama' => 'Perencanaan', 'urutan' => 1],
                ['nama' => 'Keuangan', 'urutan' => 2],
                ['nama' => 'Kepegawaian', 'urutan' => 3],
                ['nama' => 'Pendidikan dan Pelatihan', 'urutan' => 4],
                ['nama' => 'Penelitian dan Pengembangan', 'urutan' => 5],
            ],
            'F' => [
                ['nama' => 'Inspektorat', 'urutan' => 1],
            ],
            'G' => [
                ['nama' => 'Kecamatan', 'urutan' => 1],
            ],
            'H' => [
                ['nama' => 'Kesatuan Bangsa dan Politik', 'urutan' => 1],
            ],
        ];

        $childFolderMap = [];
        foreach ($subFolders as $rootCode => $children) {
            $root = $rootMap[$rootCode];
            $childIdx = 1;
            foreach ($children as $c) {
                $code = $rootCode . '.' . str_pad($childIdx, 2, '0', STR_PAD_LEFT);
                $folder = KelompokUrusan::updateOrCreate(
                    ['parent_id' => $root->id, 'nama' => $c['nama']],
                    ['kode' => $code, 'urutan' => $c['urutan']]
                );
                $childFolderMap[$c['nama']] = $folder;
                $childIdx++;
            }
        }

        // 3. Tables / Urusan Files inside Folders
        $folderPendidikan = $childFolderMap['Pendidikan'];
        $tablesPendidikan = [
            ['nama' => 'Jumlah Sekolah', 'slug' => 'jumlah-sekolah', 'urutan' => 1],
            ['nama' => 'Jumlah Siswa', 'slug' => 'jumlah-siswa', 'urutan' => 2],
            ['nama' => 'Jumlah Ruang Kelas', 'slug' => 'jumlah-ruang-kelas', 'urutan' => 3],
            ['nama' => 'Jumlah Guru', 'slug' => 'jumlah-guru', 'urutan' => 4],
        ];

        foreach ($tablesPendidikan as $tp) {
            Urusan::updateOrCreate(
                ['slug' => $tp['slug']],
                [
                    'kelompok_urusan_id' => $folderPendidikan->id,
                    'nama_urusan' => $tp['nama'],
                    'urutan' => $tp['urutan'],
                    'nomor' => null,
                    'icon' => 'bi-file-earmark-text',
                    'status_aktif' => true,
                ]
            );
        }

        $folderKesehatan = $childFolderMap['Kesehatan'];
        Urusan::updateOrCreate(
            ['slug' => 'jumlah-fasilitas-pelayanan-kesehatan'],
            [
                'kelompok_urusan_id' => $folderKesehatan->id,
                'nama_urusan' => 'Jumlah Fasilitas Pelayanan Kesehatan',
                'urutan' => 1,
                'nomor' => null,
                'icon' => 'bi-file-earmark-text',
                'status_aktif' => true,
            ]
        );
    }
}
