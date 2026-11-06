<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataIndikator extends Model
{
    use HasFactory;

    protected $table = 'data_indikator';

    protected $fillable = [
        'indikator_id',
        'tahun_id',
        'nilai',
        'sumber_data',
        'keterangan',
        'custom_fields',
        'updated_by',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($di) {
            if ($di->tahun_id) {
                $t = Tahun::find($di->tahun_id);
                if ($t && !$t->status_aktif) {
                    $t->status_aktif = true;
                    $t->save();
                }
            }
        });
    }

    public function getCustomValue(string $key, $default = null)
    {
        if (is_array($this->custom_fields) && array_key_exists($key, $this->custom_fields)) {
            return $this->custom_fields[$key];
        }
        return $default;
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function tahun()
    {
        return $this->belongsTo(Tahun::class, 'tahun_id');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFormattedNilaiAttribute(): string
    {
        if ($this->nilai === null || $this->nilai === '') {
            return '—';
        }

        // If numeric, format nicely without losing decimals
        if (is_numeric($this->nilai)) {
            $num = (float) $this->nilai;
            // Check if whole number or float
            if (floor($num) == $num) {
                return number_format($num, 0, ',', '.');
            } else {
                // Remove trailing zeros after decimal point
                return rtrim(rtrim(number_format($num, 4, ',', '.'), '0'), ',');
            }
        }

        return (string) $this->nilai;
    }
}
