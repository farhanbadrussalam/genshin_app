<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Ambil nilai setting berdasarkan key (dengan in-memory / redis cache 24 jam)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting_{$key}", 86400, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if (!$record || $record->value === null) {
                return $default;
            }

            // Decode jika JSON
            $decoded = json_decode($record->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $record->value;
        });
    }

    /**
     * Simpan nilai setting ke database dan refresh cache
     */
    public static function set(string $key, mixed $value, ?string $description = null): static
    {
        $serializedValue = is_array($value) || is_object($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string)$value;

        $record = static::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $serializedValue,
                'description' => $description ?? ($key === 'gemini_ai_config' ? 'Konfigurasi Model & Limit Google Gemini AI' : null),
            ]
        );

        Cache::forget("app_setting_{$key}");

        return $record;
    }
}

