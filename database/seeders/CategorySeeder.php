<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $make = function (string $name, ?Category $parent = null): Category {
            // Para que el slug sea único global (tu migración lo exige),
            // los hijos usan el slug-del-padre + nombre
            $base = $parent
                ? Str::slug($parent->slug.' '.$name)
                : Str::slug($name);

            $slug = $base;
            $k = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$k}";
                $k++;
            }

            return Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'slug' => $slug, 'parent_id' => $parent?->id]
            );
        };

        // Nivel 1
        $motos      = $make('Motos');
        $accesorios = $make('Accesorios');

        // Nivel 2 bajo Motos (marcas)
        $yamaha = $make('Yamaha', $motos);
        $honda  = $make('Honda',  $motos);
        $suzuki = $make('Suzuki', $motos);
        $bajaj  = $make('Bajaj',  $motos);
        $akt    = $make('AKT',    $motos);

        // Nivel 3 bajo cada marca (partes)
        $parts = ['Empaques', 'Bandas', 'Pastillas', 'Guayas'];
        foreach ([$yamaha, $honda, $suzuki, $bajaj, $akt] as $brand) {
            foreach ($parts as $p) {
                $make($p, $brand);
            }
        }

        // Nivel 2 bajo Accesorios
        $make('Cascos',  $accesorios);
        $make('Llantas', $accesorios);
        $make('Guantes', $accesorios);
    }
}
