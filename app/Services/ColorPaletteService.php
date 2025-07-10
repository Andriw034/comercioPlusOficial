<?php
namespace App\Services;

use ColorThief\ColorThief;

class ColorPaletteService
{
    /** @return array{primaryColor:string, backgroundColor:string, textColor:string} */
    public function generateTheme(string $logoAbsolutePath, string $coverAbsolutePath): array
    {
        $logoPalette = $this->toHexPalette(ColorThief::getPalette($logoAbsolutePath, 8, 10));
        $coverPalette = $this->toHexPalette(ColorThief::getPalette($coverAbsolutePath, 8, 10));

        // 1) Primary = color dominante del logo (1er color de la paleta)
        $primary = $logoPalette[0] ?? '#FFA14F';

        // 2) Background = color más claro de la portada
        $background = $this->pickLightest($coverPalette) ?? '#ffffff';

        // 3) Text color con contraste suficiente frente a background
        $text = $this->idealTextColor($background);

        return [
            'primaryColor' => $primary,
            'backgroundColor' => $background,
            'textColor' => $text,
        ];
    }

    /** @param array<int,array{0:int,1:int,2:int}> $rgbList */
    private function toHexPalette(array $rgbList): array
    {
        return array_map(function ($rgb) {
            return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
        }, $rgbList);
    }

    /** @param string[] $palette */
    private function pickLightest(array $palette): ?string
    {
        if (!$palette) return null;
        $lightest = null; $max = -1;
        foreach ($palette as $hex) {
            $lum = $this->relativeLuminance($hex);
            if ($lum > $max) { $max = $lum; $lightest = $hex; }
        }
        return $lightest;
    }

    private function idealTextColor(string $backgroundHex): string
    {
        // Contraste simple: si la luminancia es alta, texto oscuro; si es baja, texto claro
        return $this->relativeLuminance($backgroundHex) > 0.6 ? '#111827' : '#FFFFFF';
    }

    private function relativeLuminance(string $hex): float
    {
        [$r,$g,$b] = $this->hexToRgb($hex);
        $srgb = [ $r/255, $g/255, $b/255 ];
        $lin = array_map(fn($c) => $c <= 0.03928 ? $c/12.92 : pow(($c+0.055)/1.055, 2.4), $srgb);
        return 0.2126*$lin[0] + 0.7152*$lin[1] + 0.0722*$lin[2];
    }

    /** @return array{0:int,1:int,2:int} */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    }
}
