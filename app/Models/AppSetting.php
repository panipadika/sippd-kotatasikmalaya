<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key with optional default.
     */
    public static function getValue(string $key, $default = null): ?string
    {
        return Cache::remember("app_setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, ?string $value, ?string $description = null): void
    {
        $data = ['value' => $value];
        if ($description !== null) {
            $data['description'] = $description;
        }

        static::updateOrCreate(['key' => $key], $data);
        Cache::forget("app_setting.{$key}");
    }

    /**
     * Get all settings within a group.
     */
    public static function getGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->get();
    }

    /**
     * Helper: get the current admin slug for login URLs.
     */
    public static function getSuperAdminSlug(): string
    {
        return static::getValue('superadmin_login_slug', 'superadmin');
    }

    /**
     * Helper: get the current admin slug for login URLs.
     */
    public static function getAdminSlug(): string
    {
        return static::getValue('admin_login_slug', 'admin');
    }

    /**
     * Helper: get the current user slug for login URLs.
     */
    public static function getUserSlug(): string
    {
        return static::getValue('user_login_slug', 'portal');
    }
}
