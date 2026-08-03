<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Busqueda de compatibilidad de repuestos por lenguaje natural.
 *
 * Antes esta logica vivia en el frontend con listas de marcas y modelos escritas
 * a mano, y devolvia texto fijo cuando no encontraba nada. Aca se resuelve contra
 * los datos reales y, si no hay resultado, se dice: nunca se inventa una referencia.
 */
class PartsAssistantService
{
    /** Distancia maxima de Levenshtein para aceptar una correccion de tipeo. */
    private const MAX_TYPO_DISTANCE = 2;

    /** Palabras sueltas que el usuario usa para nombrar cada tipo de pieza. */
    private const PART_TYPE_SYNONYMS = [
        'banda'            => ['banda', 'bandas', 'correa', 'correas'],
        'pastilla_freno'   => ['pastilla', 'pastillas', 'freno', 'frenos', 'balata', 'balatas'],
        'bujia'            => ['bujia', 'bujias'],
        'cadena'           => ['cadena', 'cadenas'],
        'filtro_aceite'    => ['filtro de aceite', 'filtro aceite', 'filtro'],
        'filtro_aire'      => ['filtro de aire', 'filtro aire'],
        'kit_arrastre'     => ['kit', 'arrastre', 'kit de arrastre'],
        'embrague'         => ['embrague', 'clutch', 'croche'],
        'rodamiento'       => ['rodamiento', 'rodamientos', 'balinera', 'balineras'],
        'pinon_motor'      => ['pinon', 'pinion'],
        'catalina'         => ['catalina', 'sprocket'],
        'caucho_carburador'=> ['caucho', 'carburador'],
        // Los tres de abajo son de los mas preguntados en el mostrador y no estaban:
        // sin la palabra aca, cargar los datos no sirve de nada porque el buscador
        // nunca reconoce lo que escribe el cliente.
        'retenedor'        => ['retenedor', 'retenedores', 'reten', 'retenes', 'estopera', 'estoperas'],
        'guaya'            => ['guaya', 'guayas'],
        'rodamiento_direccion' => [
            'rodamiento de direccion', 'rodamientos de direccion',
            'balinera de direccion', 'balineras de direccion',
            'kit de direccion', 'juego de direccion',
        ],
    ];

    /** Etiquetas legibles para el usuario final. */
    private const PART_TYPE_LABELS = [
        'banda'             => 'Banda de transmision',
        'pastilla_freno'    => 'Pastillas de freno',
        'bujia'             => 'Bujia',
        'cadena'            => 'Cadena',
        'filtro_aceite'     => 'Filtro de aceite',
        'filtro_aire'       => 'Filtro de aire',
        'kit_arrastre'      => 'Kit de arrastre',
        'embrague'          => 'Embrague',
        'rodamiento'        => 'Rodamiento',
        'pinon_motor'       => 'Pinon motor',
        'catalina'          => 'Catalina',
        'caucho_carburador' => 'Caucho de carburador',
        'retenedor'         => 'Retenedor',
        'guaya'             => 'Guaya',
        'rodamiento_direccion' => 'Rodamiento de direccion',
    ];

    /**
     * Tipos que el buscador sabe reconocer cuando el cliente escribe la pregunta.
     *
     * Lo usa el importador para avisar si un CSV trae un tipo que nadie va a poder
     * encontrar: cargar datos que el buscador no alcanza es trabajo perdido.
     *
     * @return list<string>
     */
    public static function tiposBuscables(): array
    {
        return array_keys(self::PART_TYPE_SYNONYMS);
    }

    public function search(string $question, ?int $storeId = null): array
    {
        $normalized  = $this->normalize($question);
        $corrections = [];
        $consumed    = [];

        // El tipo de pieza se detecta PRIMERO y su palabra se marca como usada.
        // Si no, "vanda" (por "banda") se corrige antes a "Honda" y la consulta
        // termina respondiendo con repuestos de otra marca.
        $partType = $this->matchPartType($normalized, $corrections, $consumed);
        $brand    = $this->matchBrand($normalized, $corrections, $consumed);
        $model    = $this->matchModel($normalized, $brand, $corrections, $consumed);
        $year     = $this->matchYear($question);

        ['rows' => $matches, 'alcance' => $alcance] = $this->findCompatibilities($brand, $model, $year, $partType);

        // Solo se cruza con inventario si hay algo que cruzar.
        $withStock = $matches->map(function (object $row) use ($storeId): array {
            return $this->toCompatibilityArray($row, $storeId);
        })->values()->all();

        return [
            'interpretacion' => [
                'marca'                  => $brand,
                'modelo'                 => $model,
                'anio'                   => $year,
                'tipo_pieza'             => $partType,
                'tipo_pieza_label'       => $partType ? (self::PART_TYPE_LABELS[$partType] ?? $partType) : null,
                'correcciones_aplicadas' => $corrections,
            ],
            'alcance'            => $alcance,
            'aviso'              => $this->scopeWarning($alcance, $brand, $model, $year),
            'compatibilidades'   => $withStock,
            'sugerencias'        => $this->buildSuggestions($brand, $model, $partType, $matches),
            'sin_resultados_por' => $this->diagnose($brand, $model, $partType, $matches),
            'motos_con_datos'    => $matches->isEmpty() ? $this->motorcyclesWithData() : [],
        ];
    }

    /**
     * Texto que la UI debe mostrar cuando el resultado no corresponde exactamente
     * a lo que se pregunto. Sin esto, el usuario cree que la pieza le sirve.
     */
    private function scopeWarning(string $alcance, ?string $brand, ?string $model, ?int $year): ?string
    {
        return match ($alcance) {
            'moto_otros_anios' => $year !== null
                ? "No hay datos para el año {$year}. Se muestran otros años de la {$model}: confirma el año antes de vender."
                : null,
            'moto_otras_piezas' => "No hay datos de esa pieza para la {$model}. Se muestran otras piezas de esa moto.",
            'otras_motos_de_la_marca' => "No hay datos para esa moto {$brand}. Se muestran piezas de OTRAS motos {$brand}: no asumas que le sirven.",
            'otras_motos' => 'No hay datos para esa moto. Se muestran piezas de otras motos, solo como referencia.',
            default => null,
        };
    }

    // ── Normalizacion ────────────────────────────────────────────────────

    private function normalize(string $text): string
    {
        $lower = mb_strtolower(trim($text), 'UTF-8');
        $ascii = strtr($lower, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9\s]/', ' ', $ascii)));
    }

    /**
     * Palabras que nombran piezas y NUNCA deben corregirse a marca o modelo.
     * Sin este guard, "banda" se corrige a "honda" (distancia 2) y una consulta
     * sobre una Yamaha termina respondiendo con repuestos Honda.
     */
    private function protectedWords(): array
    {
        static $words = null;

        if ($words === null) {
            $words = [];
            foreach (self::PART_TYPE_SYNONYMS as $synonyms) {
                foreach ($synonyms as $synonym) {
                    foreach (explode(' ', $synonym) as $word) {
                        $words[$word] = true;
                    }
                }
            }
        }

        return $words;
    }

    /** Coincidencia literal, sin tolerancia a errores. */
    private function exactContains(string $haystack, string $needle): bool
    {
        return $needle !== '' && str_contains($haystack, $needle);
    }

    /**
     * Coincidencia tolerante a errores de tipeo: "inigtor" encuentra "ignitor".
     * Solo se usa cuando la busqueda exacta no encontro nada.
     */
    private function fuzzyContains(string $haystack, string $needle, ?string &$typo = null, array $consumed = []): bool
    {
        // Palabras cortas no se corrigen: con distancia 2 casi todo coincide.
        if ($needle === '' || mb_strlen($needle) < 5) {
            return false;
        }

        $needleWords = explode(' ', $needle);
        $words       = explode(' ', $haystack);
        $windowSize  = count($needleWords);
        $protected   = $this->protectedWords();

        $best     = null;
        $bestDist = PHP_INT_MAX;

        for ($i = 0; $i + $windowSize <= count($words); $i++) {
            $slice  = array_slice($words, $i, $windowSize);
            $window = implode(' ', $slice);

            // Palabras que nombran piezas, o ya usadas por otro campo, no se
            // reinterpretan como marca o modelo.
            if ($windowSize === 1 && (isset($protected[$slice[0]]) || isset($consumed[$slice[0]]))) {
                continue;
            }

            $dist = levenshtein($window, $needle);

            // Se queda con la correccion mas cercana, no con la primera que entre.
            if ($dist > 0 && $dist <= self::MAX_TYPO_DISTANCE && $dist < $bestDist) {
                $best     = $window;
                $bestDist = $dist;
            }
        }

        if ($best !== null) {
            $typo = $best;

            return true;
        }

        return false;
    }

    // ── Deteccion contra datos reales ────────────────────────────────────

    /**
     * Marcas conocidas. Se unen las dos fuentes porque no coinciden:
     * `motorcycle_models` es el catalogo de motos y `parts_compatibility` tiene
     * motos con datos de repuestos que no estan en el catalogo (p. ej. Viva R).
     * Al asistente le sirven ambas.
     */
    private function knownBrands(): Collection
    {
        return DB::table('motorcycle_models')->distinct()->pluck('brand')
            ->merge(DB::table('parts_compatibility')->distinct()->pluck('motorcycle_brand'))
            ->unique()
            ->values();
    }

    /** Modelos conocidos, de ambas fuentes. Ver knownBrands(). */
    private function knownModels(?string $brand): Collection
    {
        $catalog = DB::table('motorcycle_models')->select('model');
        $parts   = DB::table('parts_compatibility')->select('motorcycle_model as model');

        if ($brand !== null) {
            $catalog->where('brand', $brand);
            $parts->where('motorcycle_brand', $brand);
        }

        return $catalog->pluck('model')
            ->merge($parts->pluck('model'))
            ->unique()
            // Modelos largos primero: "pulsar ns 200" antes que "pulsar".
            ->sortByDesc(fn (string $m): int => mb_strlen($m))
            ->values();
    }

    private function matchBrand(string $text, array &$corrections, array &$consumed): ?string
    {
        $brands = $this->knownBrands();

        // Primera pasada: coincidencia literal. Una marca escrita bien siempre
        // le gana a otra que solo coincide por correccion de tipeo.
        foreach ($brands as $brand) {
            $needle = $this->normalize($brand);
            if ($this->exactContains($text, $needle)) {
                $consumed[$needle] = true;

                return $brand;
            }
        }

        foreach ($brands as $brand) {
            $typo = null;
            if ($this->fuzzyContains($text, $this->normalize($brand), $typo, $consumed)) {
                $corrections[]   = ['escribiste' => $typo, 'entendimos' => $brand];
                $consumed[$typo] = true;

                return $brand;
            }
        }

        return null;
    }

    private function matchModel(string $text, ?string $brand, array &$corrections, array &$consumed): ?string
    {
        $models = $this->knownModels($brand);

        foreach ($models as $model) {
            if ($this->exactContains($text, $this->normalize($model))) {
                return $model;
            }
        }

        // Nadie escribe el nombre completo: dicen "viva r" por "Viva R Style" y
        // "pulsar 200" por "Pulsar 200 NS". Se acepta el prefijo del modelo.
        $byPrefix = null;
        $bestLen  = 0;

        foreach ($models as $model) {
            $tokens = explode(' ', $this->normalize($model));

            for ($k = count($tokens); $k >= 1; $k--) {
                $prefix = implode(' ', array_slice($tokens, 0, $k));

                // Un solo token debe ser distintivo para no matchear por "125".
                if ($k === 1 && (mb_strlen($prefix) < 4 || is_numeric($prefix))) {
                    continue;
                }

                if ($this->exactContains($text, $prefix) && mb_strlen($prefix) > $bestLen) {
                    $byPrefix = $model;
                    $bestLen  = mb_strlen($prefix);
                    break;
                }
            }
        }

        if ($byPrefix !== null) {
            return $byPrefix;
        }

        foreach ($models as $model) {
            $typo = null;
            if ($this->fuzzyContains($text, $this->normalize($model), $typo, $consumed)) {
                $corrections[]   = ['escribiste' => $typo, 'entendimos' => $model];
                $consumed[$typo] = true;

                return $model;
            }
        }

        return null;
    }

    private function matchYear(string $text): ?int
    {
        if (preg_match('/\b(19[9]\d|20[0-4]\d)\b/', $text, $m) === 1) {
            return (int) $m[1];
        }

        return null;
    }

    private function matchPartType(string $text, array &$corrections, array &$consumed): ?string
    {
        // Sinonimos largos primero: "filtro de aceite" antes que "filtro".
        $pairs = [];
        foreach (self::PART_TYPE_SYNONYMS as $type => $synonyms) {
            foreach ($synonyms as $synonym) {
                $pairs[] = [$type, $synonym];
            }
        }
        usort($pairs, fn (array $a, array $b): int => mb_strlen($b[1]) <=> mb_strlen($a[1]));

        foreach ($pairs as [$type, $synonym]) {
            if ($this->exactContains($text, $synonym)) {
                foreach (explode(' ', $synonym) as $word) {
                    $consumed[$word] = true;
                }

                return $type;
            }
        }

        foreach ($pairs as [$type, $synonym]) {
            $typo = null;
            if ($this->fuzzyContains($text, $synonym, $typo)) {
                $corrections[]   = ['escribiste' => $typo, 'entendimos' => $synonym];
                $consumed[$typo] = true;

                return $type;
            }
        }

        return null;
    }

    // ── Busqueda en cascada ──────────────────────────────────────────────

    /**
     * Cascada de lo mas especifico a lo mas general. Cada nivel declara su
     * "alcance" para que la UI pueda avisar cuando el resultado ya no es de la
     * moto que se pregunto: devolver piezas de otra moto sin decirlo induce a
     * vender la pieza equivocada.
     *
     * @return array{rows: Collection, alcance: string}
     */
    private function findCompatibilities(?string $brand, ?string $model, ?int $year, ?string $partType): array
    {
        $levels = [];

        if ($model !== null) {
            if ($year !== null && $partType !== null) {
                $levels[] = ['brand' => $brand, 'model' => $model, 'year' => $year, 'type' => $partType, 'alcance' => 'moto_exacta'];
            }
            if ($partType !== null) {
                $levels[] = ['brand' => $brand, 'model' => $model, 'year' => null, 'type' => $partType, 'alcance' => 'moto_otros_anios'];
            }
            $levels[] = ['brand' => $brand, 'model' => $model, 'year' => null, 'type' => null, 'alcance' => 'moto_otras_piezas'];
        }

        // Sin modelo reconocido solo se puede ampliar a la marca, y hay que decirlo.
        if ($brand !== null && $partType !== null) {
            $levels[] = ['brand' => $brand, 'model' => null, 'year' => null, 'type' => $partType, 'alcance' => 'otras_motos_de_la_marca'];
        }

        if ($partType !== null) {
            $levels[] = ['brand' => null, 'model' => null, 'year' => null, 'type' => $partType, 'alcance' => 'otras_motos'];
        }

        foreach ($levels as $level) {
            // Cuanto mas lejos del pedido, menos resultados: mostrar 30 piezas de
            // motos ajenas no ayuda, abruma.
            $limit = match ($level['alcance']) {
                'moto_exacta', 'moto_otros_anios' => 30,
                'moto_otras_piezas'               => 15,
                default                           => 8,
            };

            $rows = $this->queryCompatibilities($level, $limit);
            if ($rows->isNotEmpty()) {
                return ['rows' => $rows, 'alcance' => $level['alcance']];
            }
        }

        return ['rows' => collect(), 'alcance' => 'sin_resultados'];
    }

    private function queryCompatibilities(array $level, int $limit = 30): Collection
    {
        $query = DB::table('parts_compatibility');

        if ($level['brand'] !== null) {
            $query->where('motorcycle_brand', $level['brand']);
        }

        if ($level['model'] !== null) {
            $query->where('motorcycle_model', 'like', '%' . $level['model'] . '%');
        }

        if ($level['type'] !== null) {
            $query->where('part_type', $level['type']);
        }

        if ($level['year'] !== null) {
            $query->where('year_from', '<=', $level['year'])
                ->where('year_to', '>=', $level['year']);
        }

        return $query->orderBy('part_type')->orderBy('part_brand')->limit($limit)->get();
    }

    // ── Cruce con el inventario de la tienda ─────────────────────────────

    private function toCompatibilityArray(object $row, ?int $storeId): array
    {
        return [
            'referencia'   => $row->part_reference,
            'marca'        => $row->part_brand,
            'tipo'         => $row->part_type,
            'tipo_label'   => self::PART_TYPE_LABELS[$row->part_type] ?? $row->part_type,
            'descripcion'  => $row->part_description,
            'moto'         => trim($row->motorcycle_brand . ' ' . $row->motorcycle_model),
            'anios'        => $row->year_from . '-' . $row->year_to,
            'notas'        => $row->notes,
            'en_inventario'=> $storeId !== null ? $this->findInInventory($row->part_reference, $storeId) : null,
        ];
    }

    /**
     * Busca la referencia en el inventario de la tienda: por SKU, por codigo
     * registrado o por nombre. Devuelve null si no hay coincidencia, que hoy es
     * lo normal porque los productos cargados no usan referencias de catalogo.
     */
    private function findInInventory(string $reference, int $storeId): ?array
    {
        $product = DB::table('products')
            ->where('store_id', $storeId)
            ->where(function ($q) use ($reference) {
                $q->where('sku', $reference)
                    ->orWhere('ref_adicional', $reference)
                    ->orWhere('name', 'like', '%' . $reference . '%')
                    ->orWhereIn('id', function ($sub) use ($reference) {
                        $sub->select('product_id')->from('product_codes')->where('value', $reference);
                    });
            })
            ->select('id', 'name', 'price', 'stock')
            ->first();

        if ($product === null) {
            return null;
        }

        return [
            'producto_id' => $product->id,
            'nombre'      => $product->name,
            'precio'      => (float) $product->price,
            'stock'       => (int) $product->stock,
            'disponible'  => $product->stock > 0,
        ];
    }

    // ── Sugerencias y diagnostico ────────────────────────────────────────

    /** Otras piezas de la misma moto, para ofrecerle algo mas al cliente. */
    private function buildSuggestions(?string $brand, ?string $model, ?string $partType, Collection $matches): array
    {
        if ($matches->isEmpty() || $model === null) {
            return [];
        }

        $query = DB::table('parts_compatibility')
            ->where('motorcycle_model', 'like', '%' . $model . '%');

        if ($brand !== null) {
            $query->where('motorcycle_brand', $brand);
        }

        if ($partType !== null) {
            $query->where('part_type', '!=', $partType);
        }

        return $query->select('part_type')
            ->distinct()
            ->limit(5)
            ->pluck('part_type')
            ->map(fn (string $t): array => [
                'tipo'  => $t,
                'label' => self::PART_TYPE_LABELS[$t] ?? $t,
            ])
            ->values()
            ->all();
    }

    /** Por que no hubo resultados. Permite que la UI diga la verdad. */
    private function diagnose(?string $brand, ?string $model, ?string $partType, Collection $matches): ?string
    {
        if ($matches->isNotEmpty()) {
            return null;
        }

        if ($brand === null && $model === null) {
            return 'moto_desconocida';
        }

        if ($partType !== null) {
            return 'sin_datos_de_esa_pieza_para_esa_moto';
        }

        return 'sin_datos_de_esa_moto';
    }

    /** Motos que si tienen compatibilidad cargada, para orientar al usuario. */
    private function motorcyclesWithData(): array
    {
        return DB::table('parts_compatibility')
            ->select('motorcycle_brand', 'motorcycle_model')
            ->distinct()
            ->orderBy('motorcycle_brand')
            ->orderBy('motorcycle_model')
            ->get()
            ->map(fn (object $r): string => trim($r->motorcycle_brand . ' ' . $r->motorcycle_model))
            ->all();
    }
}
