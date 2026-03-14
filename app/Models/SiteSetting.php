<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $record = static::find($key);
        if (!$record) return $default;
        $decoded = json_decode($record->value, true);
        return $decoded !== null ? $decoded : $record->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }
}
