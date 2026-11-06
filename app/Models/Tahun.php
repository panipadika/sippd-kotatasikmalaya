<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahun extends Model
{
    use HasFactory;

    protected $table = 'tahun';

    protected $fillable = [
        'tahun',
        'status_aktif',
        'is_default',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'status_aktif' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function dataIndikators()
    {
        return $this->hasMany(DataIndikator::class, 'tahun_id');
    }

    public static function getDefaultTahun(): ?int
    {
        $default = static::where('status_aktif', true)->where('is_default', true)->first();
        if ($default) {
            return $default->tahun;
        }
        $latest = static::where('status_aktif', true)->orderByDesc('tahun')->first();
        return $latest ? $latest->tahun : 2026;
    }

    /**
     * Memindai struktur kolom tabel urusan dan data indikator, mengaktifkan semua tahun
     * yang terdaftar di sistem, dan menyetel tahun terbaru yang baru dibuat sebagai default.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function syncActiveYearsFromSystem(): \Illuminate\Database\Eloquent\Collection
    {
        $detectedYears = [];

        // 1. Ekstrak dari seluruh Urusan (kolom horizontal, sub-kolom, dan config tahun tabel)
        try {
            $urusans = \App\Models\Urusan::all();
            foreach ($urusans as $u) {
                if (method_exists($u, 'extractAllYearsFromConfig')) {
                    $years = $u->extractAllYearsFromConfig();
                    foreach ($years as $yr) {
                        $detectedYears[$yr] = true;
                    }
                }
            }

            // 2. Ekstrak dari baris indikator bertipe tahun vertikal
            $indYears = \App\Models\Indikator::whereRaw("nama_indikator REGEXP '^[0-9]{4}$'")->pluck('nama_indikator');
            foreach ($indYears as $iy) {
                $y = (int) $iy;
                if ($y >= 2020 && $y <= 2050) {
                    $detectedYears[$y] = true;
                }
            }

            // 3. Ekstrak dari data indikator yang terisi
            $diYears = \App\Models\DataIndikator::join('tahun', 'data_indikator.tahun_id', '=', 'tahun.id')
                ->whereNotNull('data_indikator.nilai')
                ->where('data_indikator.nilai', '!=', '')
                ->where('data_indikator.nilai', '!=', '-')
                ->pluck('tahun.tahun');
            foreach ($diYears as $dy) {
                $y = (int) $dy;
                if ($y >= 2020 && $y <= 2050) {
                    $detectedYears[$y] = true;
                }
            }
        } catch (\Throwable $e) {
            // fallback gracefully jika database belum siap
        }

        $validYears = array_keys($detectedYears);
        rsort($validYears); // urutkan descending (e.g. 2026, 2025, 2024, 2023)

        if (!empty($validYears)) {
            // Aktifkan tahun-tahun terdaftar
            foreach ($validYears as $vy) {
                $t = static::firstOrCreate(['tahun' => $vy], [
                    'status_aktif' => true,
                    'is_default' => false,
                    'keterangan' => 'Tahun Anggaran ' . $vy
                ]);
                if (!$t->status_aktif) {
                    $t->status_aktif = true;
                    $t->save();
                }
            }

            // Nonaktifkan tahun yang belum dibuat (seperti 2027, 2028)
            static::whereNotIn('tahun', $validYears)->update([
                'status_aktif' => false,
                'is_default' => false
            ]);

            // Tahun default: gunakan tahun terbaru yang baru dibuat (tahun tertinggi dari $validYears, yaitu 2026)
            $newestYear = $validYears[0];
            static::where('tahun', '!=', $newestYear)->update(['is_default' => false]);
            static::where('tahun', $newestYear)->update(['is_default' => true, 'status_aktif' => true]);
        }

        return static::where('status_aktif', true)->orderByDesc('tahun')->get();
    }

    /**
     * Mendaftarkan atau mengaktifkan tahun secara otomatis saat terdeteksi di kolom/baris/data sistem.
     * Tidak mengubah is_default yang sedang aktif.
     *
     * @param int $year
     * @return \App\Models\Tahun|null
     */
    public static function autoRegisterYear(int $year): ?self
    {
        if ($year < 2020 || $year > 2050) {
            return null;
        }

        $tahun = static::firstOrCreate(
            ['tahun' => $year],
            [
                'status_aktif' => true,
                'is_default' => false,
                'keterangan' => 'Tahun Anggaran ' . $year,
            ]
        );

        if (!$tahun->status_aktif) {
            $tahun->status_aktif = true;
            $tahun->save();
        }

        return $tahun;
    }
}
