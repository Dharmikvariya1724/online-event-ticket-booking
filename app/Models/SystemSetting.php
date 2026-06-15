<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'updated_by_admin_id',
    ];

    protected $casts = [
        'updated_by_admin_id' => 'integer',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'updated_by_admin_id');
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('system_settings')) {
                return $default;
            }
        } catch (\Throwable $e) {
            return $default;
        }
        $all = self::allCached();
        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value, ?string $type = null, ?int $adminId = null): self
    {
        $record = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::serialiseValue($value, $type),
                'type'  => $type ?? self::inferType($value),
                'updated_by_admin_id' => $adminId,
            ]
        );

        self::flushCache();

        return $record;
    }

    public static function allCached(): array
    {
        try {
            if (!Schema::hasTable('system_settings')) {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }
        return Cache::rememberForever('system_settings', function () {
            return self::query()
                ->get()
                ->mapWithKeys(function (SystemSetting $setting) {
                    return [$setting->key => $setting->castValue()];
                })
                ->toArray();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('system_settings');
    }

    public function castValue(): mixed
    {
        return self::castFromStorage($this->value, $this->type);
    }

    public static function castFromStorage(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    public static function serialiseValue(mixed $value, ?string $type = null): string
    {
        $type = $type ?? self::inferType($value);
        if ($type === 'json') {
            return json_encode($value);
        }
        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }
        return (string) $value;
    }

    public static function inferType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_array($value)) {
            return 'json';
        }
        return 'string';
    }
}
