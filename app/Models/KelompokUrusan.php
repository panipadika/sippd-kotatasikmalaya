<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokUrusan extends Model
{
    use HasFactory;

    protected $table = 'kelompok_urusan';

    protected $fillable = [
        'parent_id',
        'kode',
        'nama',
        'urutan',
        'keterangan',
    ];

    public function urusans()
    {
        return $this->hasMany(Urusan::class, 'kelompok_urusan_id')->orderBy('urutan');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan');
    }
}
