<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'label',
        'description'
    ];

    // Helper pour récupérer une valeur
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Cast selon le type
        switch ($setting->type) {
            case 'number':
                return (float) $setting->value;
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    // Helper pour définir une valeur
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'category' => 'general',
                'label' => $key
            ]);
        }

        return $value;
    }
}
