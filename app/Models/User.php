<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah role Super Admin (Akses Tertinggi)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Cek apakah role Admin atau Super Admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Cek apakah role User biasa (Viewer / Read-only)
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Cek hak kelola akun pengguna sistem (Khusus Super Admin)
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Cek hak kelola master tahun anggaran & struktur inti urusan (Khusus Super Admin)
     */
    public function canManageMasterData(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Cek hak menginput / memperbarui data nilai indikator & tabel (Super Admin & Admin)
     */
    public function canEditData(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Label manusiawi untuk Role
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'user' => 'User',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    /**
     * CSS Badge class untuk Role
     */
    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'bg-danger text-white',
            'admin' => 'bg-primary text-white',
            'user' => 'bg-secondary text-white',
            default => 'bg-light text-dark border',
        };
    }

    /**
     * Backward compatibility helper
     */
    public function isOperator(): bool
    {
        return $this->role === 'admin';
    }

    public function dataIndikators()
    {
        return $this->hasMany(DataIndikator::class, 'updated_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
