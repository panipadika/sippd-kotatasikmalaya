<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Urusan extends Model
{
    use HasFactory;

    protected $table = 'urusan';

    protected $fillable = [
        'kelompok_urusan_id',
        'nomor',
        'kode_urusan',
        'nama_urusan',
        'slug',
        'deskripsi',
        'custom_columns',
        'table_configs',
        'icon',
        'urutan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'nomor' => 'integer',
        'urutan' => 'integer',
        'custom_columns' => 'array',
        'table_configs' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($urusan) {
            $years = $urusan->extractAllYearsFromConfig();
            foreach ($years as $yr) {
                Tahun::autoRegisterYear($yr);
            }
        });
    }

    /**
     * Mengekstrak seluruh angka tahun valid dari table_configs dan custom_columns.
     *
     * @return array<int>
     */
    public function extractAllYearsFromConfig(): array
    {
        $years = [];
        $allTables = $this->tables;

        $walkCols = function ($cols) use (&$walkCols, &$years) {
            foreach ($cols as $c) {
                $name = trim($c['name'] ?? '');
                if (preg_match('/\b(20\d{2})\b/', $name, $m)) {
                    $y = (int) $m[1];
                    if ($y >= 2020 && $y <= 2050 && !preg_match('/(?:no|nomor|sk|uu|perda)\s*[\d\.\/]*\b' . $y . '\b/i', $name)) {
                        $years[$y] = true;
                    }
                }
                if (!empty($c['sub_columns'])) {
                    $walkCols($c['sub_columns']);
                }
            }
        };

        foreach ($allTables as $tbl) {
            if (!empty($tbl['tahun']) && is_numeric($tbl['tahun'])) {
                $y = (int) $tbl['tahun'];
                if ($y >= 2020 && $y <= 2050) {
                    $years[$y] = true;
                }
            }
            if (!empty($tbl['columns'])) {
                $walkCols($tbl['columns']);
            }
        }

        return array_keys($years);
    }

    public function getTablesAttribute(): array
    {
        if (is_array($this->table_configs) && count($this->table_configs) > 0) {
            return array_values($this->table_configs);
        }

        // Default: Tabel 1 menggunakan custom_columns yang sudah ada
        return [
            [
                'id' => 1,
                'name' => 'Tabel 1',
                'columns' => $this->effective_columns,
            ],
        ];
    }

    public function getTableConfig(int $tableId): array
    {
        $tables = $this->tables;
        foreach ($tables as $tbl) {
            if ((int) ($tbl['id'] ?? 0) === $tableId) {
                return $tbl;
            }
        }
        return $tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'columns' => []];
    }

    public function getColumnsForTable(int $tableId): array
    {
        $config = $this->getTableConfig($tableId);
        return $config['columns'] ?? [];
    }

    public function getEffectiveColumnsAttribute(): array
    {
        if (is_array($this->custom_columns)) {
            return array_map(function ($col) {
                unset($col['is_default']);
                return $col;
            }, $this->custom_columns);
        }

        return [];
    }

    public function kelompokUrusan()
    {
        return $this->belongsTo(KelompokUrusan::class, 'kelompok_urusan_id');
    }

    public function indikators()
    {
        return $this->hasMany(Indikator::class, 'urusan_id')->orderBy('urutan');
    }

    public function rootIndikators()
    {
        return $this->hasMany(Indikator::class, 'urusan_id')
            ->whereNull('parent_id')
            ->orderBy('urutan');
    }

    public function getFormattedNamaAttribute(): string
    {
        if ($this->nomor) {
            return "{$this->nomor}. {$this->nama_urusan}";
        }
        return $this->nama_urusan;
    }
}
