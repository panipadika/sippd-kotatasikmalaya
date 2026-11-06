<?php

namespace App\Services;

use App\Models\Indikator;

class DataKelengkapanEvaluator
{
    /**
     * Memeriksa apakah suatu nilai sel dianggap telah diisi dengan nilai/angka riil.
     * Mengabaikan null, string kosong, strip '-', json kosong, dan string 'null'.
     */
    public static function isValueFilled($val): bool
    {
        if ($val === null) {
            return false;
        }
        $s = trim((string) $val);
        return $s !== '' && $s !== '-' && $s !== '[]' && $s !== '{}' && $s !== 'null';
    }

    /**
     * Mengevaluasi apakah suatu baris indikator (leaf row) terisi pada tahun target.
     *
     * @param Indikator $ind
     * @param int $targetYear
     * @param \Illuminate\Support\Collection $allDataIndikator
     * @return bool
     */
    public static function isFilledForYear(Indikator $ind, int $targetYear, $allDataIndikator): bool
    {
        $u = $ind->urusan;
        if (!$u) {
            return false;
        }

        $tConfig = $u->getTableConfig($ind->table_id ?? 1);
        $columns = $tConfig['columns'] ?? [];

        // 1. Identifikasi kolom tahun horizontal (e.g. '2023', '2024 (Ton)', '2025')
        $yearCols = [];
        foreach ($columns as $c) {
            $cName = trim($c['name'] ?? '');
            if (preg_match('/(19|20)\d{2}/', $cName, $m)) {
                $yearCols[(int) $m[0]] = $c;
            }
        }

        // 2. Identifikasi kolom deskriptor (No, Uraian, Kecamatan, Komoditas, OPD, dll.)
        $descriptorColIds = [];
        $hasFoundPrimaryTextDescriptor = false;
        foreach ($columns as $c) {
            $cName = strtolower(trim($c['name'] ?? ''));
            $cid = $c['id'] ?? '';
            if (in_array($cName, ['no', 'no.', 'nomor'])) {
                $descriptorColIds[] = $cid;
            } elseif (in_array($cName, ['tahun', 'thn', 'periode', 'kecamatan', 'uraian', 'nama', 'indikator', 'nama indikator', 'komoditas', 'kelompok perikanan', 'opd', 'kelurahan', 'kondisi bangunan'])) {
                $descriptorColIds[] = $cid;
                $hasFoundPrimaryTextDescriptor = true;
            } elseif (!$hasFoundPrimaryTextDescriptor && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number') {
                $descriptorColIds[] = $cid;
                $hasFoundPrimaryTextDescriptor = true;
            }
        }

        $diRecords = $allDataIndikator->where('indikator_id', $ind->id);

        // Kasus A: Tabel memiliki kolom tahun horizontal
        if (!empty($yearCols)) {
            if (!isset($yearCols[$targetYear])) {
                return false;
            }
            $targetCol = $yearCols[$targetYear];
            foreach ($diRecords as $di) {
                $cf = $di->custom_fields ?? [];
                if (!empty($targetCol['sub_columns'])) {
                    foreach ($targetCol['sub_columns'] as $sub) {
                        if (isset($cf[$sub['id']]) && self::isValueFilled($cf[$sub['id']])) {
                            return true;
                        }
                    }
                } else {
                    if (isset($cf[$targetCol['id']]) && self::isValueFilled($cf[$targetCol['id']])) {
                        return true;
                    }
                }
                if ($di->tahun && (int) $di->tahun->tahun === $targetYear && self::isValueFilled($di->nilai)) {
                    return true;
                }
            }
            return false;
        }

        // Kasus B: Baris indikator merepresentasikan tahun vertikal (e.g. nama_indikator '2023', '2024')
        if (preg_match('/^(19|20)\d{2}$/', trim($ind->nama_indikator), $mYear)) {
            $rowYear = (int) $mYear[0];
            if ($rowYear !== $targetYear) {
                return false;
            }
            foreach ($diRecords as $di) {
                $cf = $di->custom_fields ?? [];
                foreach ($cf as $k => $v) {
                    if (!in_array($k, $descriptorColIds) && self::isValueFilled($v)) {
                        return true;
                    }
                }
                if (self::isValueFilled($di->nilai)) {
                    return true;
                }
            }
            return false;
        }

        // Kasus C: Tabel terikat pada tahun tertentu (Table Year)
        $tYear = !empty($tConfig['tahun']) ? (int) $tConfig['tahun'] : null;
        if ($tYear !== null) {
            if ($tYear !== $targetYear) {
                return false;
            }
            foreach ($diRecords as $di) {
                $cf = $di->custom_fields ?? [];
                foreach ($cf as $k => $v) {
                    if (!in_array($k, $descriptorColIds) && self::isValueFilled($v)) {
                        return true;
                    }
                }
                if (self::isValueFilled($di->nilai)) {
                    return true;
                }
            }
            return false;
        }

        // Kasus D: Indikator/Tabel umum yang datanya terikat pada tahun_id
        foreach ($diRecords as $di) {
            if ($di->tahun && (int) $di->tahun->tahun === $targetYear) {
                if (self::isValueFilled($di->nilai)) {
                    return true;
                }
                $cf = $di->custom_fields ?? [];
                foreach ($cf as $k => $v) {
                    if (!in_array($k, $descriptorColIds) && self::isValueFilled($v)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
