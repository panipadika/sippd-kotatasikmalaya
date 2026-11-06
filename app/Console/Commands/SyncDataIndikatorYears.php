<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Urusan;
use App\Models\Indikator;
use App\Models\DataIndikator;
use App\Models\Tahun;

class SyncDataIndikatorYears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indikator:sync-years';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi tahun_id DataIndikator agar sesuai dengan tahun konfigurasi tabel dan tahun baris';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai sinkronisasi tahun DataIndikator...");

        $tahunsByYear = Tahun::all()->keyBy('tahun');

        // 1. Urusan yang tabelnya terikat ke tahun spesifik (e.g. Urusan 15, 21, 23, 24)
        $urusans = Urusan::where('status_aktif', true)->get();

        foreach ($urusans as $u) {
            $tables = $u->tables;
            foreach ($tables as $t) {
                $tYear = !empty($t['tahun']) ? (int) $t['tahun'] : null;
                if ($tYear && isset($tahunsByYear[$tYear])) {
                    $targetTahun = $tahunsByYear[$tYear];
                    $targetTableId = $t['id'] ?? 1;

                    // Ambil indikator untuk tabel ini
                    $inds = Indikator::where('urusan_id', $u->id)
                        ->where(function ($q) use ($targetTableId) {
                            if ($targetTableId === 1) {
                                $q->where('table_id', 1)->orWhereNull('table_id');
                            } else {
                                $q->where('table_id', $targetTableId);
                            }
                        })->get();

                    foreach ($inds as $ind) {
                        $records = DataIndikator::where('indikator_id', $ind->id)->get();
                        foreach ($records as $r) {
                            if ($r->tahun_id !== $targetTahun->id) {
                                // Cek apakah sudah ada record di target tahun
                                $existing = DataIndikator::where('indikator_id', $ind->id)
                                    ->where('tahun_id', $targetTahun->id)
                                    ->where('id', '!=', $r->id)
                                    ->first();

                                if ($existing) {
                                    // Merge data jika r memiliki nilai dan existing kosong
                                    if (!empty($r->custom_fields) && (empty($existing->custom_fields) || $existing->custom_fields === ['col_1793088053_3_sub_1' => null, 'col_1793088053_3_sub_2' => null])) {
                                        $existing->custom_fields = array_merge($existing->custom_fields ?? [], $r->custom_fields);
                                        $existing->save();
                                    }
                                    $r->delete();
                                    $this->line("Merged & deleted duplicate DataIndikator ID {$r->id} into ID {$existing->id}");
                                } else {
                                    $oldTahun = $r->tahun ? $r->tahun->tahun : $r->tahun_id;
                                    $r->tahun_id = $targetTahun->id;
                                    $r->save();
                                    $this->line("Updated DataIndikator ID {$r->id} ({$u->nama_urusan} -> {$ind->nama_indikator}) from {$oldTahun} to {$targetTahun->tahun}");
                                }
                            }
                        }
                    }
                }
            }
        }

        // 2. Baris yang namanya adalah tahun (e.g. 2023, 2024, 2025)
        $yearRowIndikators = Indikator::where('status_aktif', true)->get();
        foreach ($yearRowIndikators as $ind) {
            $nameTrim = trim($ind->nama_indikator);
            if (preg_match('/^(19|20)\d{2}$/', $nameTrim, $m)) {
                $rowYear = (int) $m[0];
                if (isset($tahunsByYear[$rowYear])) {
                    $targetTahun = $tahunsByYear[$rowYear];
                    $records = DataIndikator::where('indikator_id', $ind->id)->get();
                    foreach ($records as $r) {
                        if ($r->tahun_id !== $targetTahun->id) {
                            $existing = DataIndikator::where('indikator_id', $ind->id)
                                ->where('tahun_id', $targetTahun->id)
                                ->where('id', '!=', $r->id)
                                ->first();

                            if ($existing) {
                                $r->delete();
                            } else {
                                $oldTahun = $r->tahun ? $r->tahun->tahun : $r->tahun_id;
                                $r->tahun_id = $targetTahun->id;
                                $r->save();
                                $this->line("Updated Row Year DataIndikator ID {$r->id} (Row: {$nameTrim}) from {$oldTahun} to {$targetTahun->tahun}");
                            }
                        }
                    }
                }
            }
        }

        $this->info("Sinkronisasi tahun DataIndikator selesai!");
        return 0;
    }
}
