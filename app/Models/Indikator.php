<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indikator extends Model
{
    use HasFactory;

    protected $table = 'indikator';

    protected $fillable = [
        'urusan_id',
        'table_id',
        'parent_id',
        'kode_indikator',
        'nama_indikator',
        'deskripsi',
        'satuan',
        'tipe_data',
        'urutan',
        'status_aktif',
        'custom_fields',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'urutan' => 'integer',
        'urusan_id' => 'integer',
        'table_id' => 'integer',
        'parent_id' => 'integer',
        'custom_fields' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($indikator) {
            $nama = trim($indikator->nama_indikator ?? '');
            if (preg_match('/^(19|20)\d{2}$/', $nama, $m)) {
                $y = (int) $m[0];
                if ($y >= 2020 && $y <= 2050) {
                    Tahun::autoRegisterYear($y);
                }
            }
        });

        static::deleting(function ($indikator) {
            // Hapus permanen semua data_indikator terkait agar tidak menumpuk di database
            $indikator->dataIndikators()->delete();

            // Hapus permanen seluruh sub-indikator (child rows) secara rekursif
            foreach ($indikator->children()->get() as $child) {
                $child->delete();
            }
        });
    }

    public function urusan()
    {
        return $this->belongsTo(Urusan::class, 'urusan_id');
    }

    public function parent()
    {
        return $this->belongsTo(Indikator::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Indikator::class, 'parent_id')->orderBy('urutan');
    }

    public function activeChildren()
    {
        return $this->hasMany(Indikator::class, 'parent_id')->where('status_aktif', true)->orderBy('urutan');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function dataIndikators()
    {
        return $this->hasMany(DataIndikator::class, 'indikator_id');
    }

    public function getAncestorsAttribute(): array
    {
        $ancestors = [];
        $current = $this->parent;
        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }
        return $ancestors;
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $current = $this->parent;
        while ($current) {
            $depth++;
            $current = $current->parent;
        }
        return $depth;
    }

    public function getNilaiByTahun(?int $tahunId)
    {
        if (!$tahunId) {
            return null;
        }
        return $this->dataIndikators->firstWhere('tahun_id', $tahunId);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasData(): bool
    {
        return $this->dataIndikators()->whereNotNull('nilai')->where('nilai', '!=', '')->exists();
    }
}
