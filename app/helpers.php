
<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

if (!function_exists('get_setting')) {
    /**
     * Obtener el valor de un setting dado una clave
     */
    function get_setting($key, $default = null)
    {
        $user = Auth::user();

        if (!$user) return $default;

        $setting = Setting::where('key', $key)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('role', 'all')
                      ->orWhere('role', $user->role);
            })
            ->latest()
            ->first();

        return $setting ? $setting->value : $default;
    }
}

if (!function_exists('set_setting')) {
    /**
     * Guardar o actualizar un setting para un usuario
     */
    function set_setting($key, $value)
    {
        $user = Auth::user();

        if (!$user) return false;

        return Setting::updateOrCreate(
            [
                'key' => $key,
                'user_id' => $user->id
            ],
            [
                'value' => $value,
                'role' => $user->role ?? 'all'
            ]
        );
    }
}
