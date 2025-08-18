<?php

namespace App\Helpers;

class CssValidator
{
    /**
     * Validate CSS color value
     *
     * @param string $color
     * @return bool
     */
    public static function isValidColor($color)
    {
        if (empty($color)) {
            return false;
        }

        $color = trim($color);

        // Hex color validation
        if (preg_match('/^#([a-f0-9]{3}){1,2}$/i', $color)) {
            return true;
        }

        // RGB/RGBA color validation
        if (preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/i', $color)) {
            return true;
        }

        if (preg_match('/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*([01]?\.?\d*)\s*\)$/i', $color)) {
            return true;
        }

        // HSL/HSLA color validation
        if (preg_match('/^hsl\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%\s*\)$/i', $color)) {
            return true;
        }

        if (preg_match('/^hsla\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%\s*,\s*([01]?\.?\d*)\s*\)$/i', $color)) {
            return true;
        }

        // Named colors
        $namedColors = [
            'transparent', 'currentColor', 'black', 'white', 'red', 'green', 'blue',
            'yellow', 'cyan', 'magenta', 'silver', 'gray', 'maroon', 'olive', 'lime',
            'aqua', 'teal', 'navy', 'fuchsia', 'purple'
        ];

        return in_array(strtolower($color), $namedColors);
    }

    /**
     * Sanitize CSS color value
     *
     * @param string $color
     * @return string
     */
    public static function sanitizeColor($color)
    {
        if (empty($color)) {
            return 'transparent';
        }

        $color = trim($color);

        // Validate and return safe color
        if (self::isValidColor($color)) {
            return $color;
        }

        return 'transparent';
    }
}
