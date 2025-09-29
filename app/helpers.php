<?php
// Archivo helpers.php creado para evitar error de carga en Composer.
// Aquí puedes agregar funciones helper globales si es necesario.



/**
 * Get the current URL
 */
function current_url()
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $protocol . $host . $uri;
}

/**
 * Set a setting value
 */
function set_setting($key, $value)
{
    $setting = \App\Models\Setting::where('key', $key)->first();

    if ($setting) {
        $setting->update(['value' => $value]);
    } else {
        \App\Models\Setting::create([
            'key' => $key,
            'value' => $value,
        ]);
    }
}

/**
 * Get a setting value
 */
function get_setting($key, $default = null)
{
    $setting = \App\Models\Setting::where('key', $key)->first();

    return $setting ? $setting->value : $default;
}
