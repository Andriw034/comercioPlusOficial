<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Services\Ai\AiTextGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Asistente conversacional de una tienda, con IA real.
 *
 * A diferencia de PartsAssistantService (busqueda estructurada de compatibilidad),
 * aca se le da a la IA el catalogo REAL de la tienda y responde como si fuera el
 * vendedor de ESA tienda. La llamada se hace desde el backend, no desde el
 * navegador: asi funciona para cualquier cliente en produccion y la API key nunca
 * queda expuesta en el frontend.
 *
 * El contexto que se le arma tiene tres capas, y esa es la razon de que pueda
 * responder "cualquier cosa" sin inventar: un resumen agregado del catalogo (para
 * preguntas tipo "que tienes"), los productos que coinciden con la pregunta, y la
 * tabla de compatibilidad verificada (que no depende de la tienda).
 *
 * Que proveedor responde (Gemini o Claude) lo decide AI_PROVIDER y no le importa a
 * esta clase: todo lo de arriba es igual para cualquiera. Ver App\Services\Ai.
 */
class StoreAssistantService
{
    /** Tope de productos y compatibilidades en el contexto: mas es ruido y tokens. */
    private const MAX_PRODUCTS = 15;
    private const MAX_COMPATIBILITIES = 15;

    /** Turnos previos que se le reenvian a la IA para que mantenga el hilo. */
    private const MAX_HISTORY = 10;

    /** Palabras vacias que no aportan a la busqueda de productos. */
    private const STOP_WORDS = [
        'que', 'cual', 'cuales', 'como', 'le', 'les', 'sirve', 'sirven', 'una', 'uno',
        'unos', 'unas', 'para', 'del', 'los', 'las', 'con', 'por', 'tiene', 'tienes',
        'tienen', 'hay', 'moto', 'ano', 'anio', 'año', 'de', 'la', 'el', 'en', 'mi',
        'me', 'un', 'y', 'o', 'a', 'es', 'necesito', 'quiero', 'busco', 'sobre',
    ];

    public function __construct(
        private readonly PartsAssistantService $parts,
        private readonly AiTextGenerator $ai,
    ) {
    }

    /**
     * @param  list<array{role: string, content: string}>  $history  Turnos previos de la conversacion.
     * @return array<string, mixed>
     */
    public function ask(string $question, int $storeId, array $history = []): array
    {
        $store = Store::query()->find($storeId);

        if ($store === null) {
            throw new RuntimeException("No existe la tienda {$storeId}");
        }

        $products = $this->findStoreProducts($store->id, $question);
        $verified = $this->verifiedCompatibility($question, $store->id);

        $answer = $this->ai->generate(
            $this->systemPrompt($store),
            $this->normalizeHistory($history),
            $this->buildUserContent($question, $store->id, $products, $verified),
        );

        return [
            'store'          => ['id' => $store->id, 'name' => $store->name],
            'question'       => $question,
            'answer'         => $answer,
            'products_found' => $products->count(),
            'products'       => $products->take(5)->map(fn (Product $p): array => [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => $p->price,
                'stock' => $p->stock,
            ])->values()->all(),
        ];
    }

    // ── Contexto ─────────────────────────────────────────────────────────

    /**
     * Resumen agregado del catalogo. Sirve para preguntas amplias ("que tienes?")
     * sin volcarle a Claude los 1877 productos de la tienda.
     */
    private function catalogSummary(int $storeId): string
    {
        /** @var object|null $stats */
        $stats = Product::query()
            ->where('store_id', $storeId)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS activos')
            ->selectRaw('SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) AS agotados')
            ->selectRaw('MIN(price) AS precio_min')
            ->selectRaw('MAX(price) AS precio_max')
            ->first();

        if ($stats === null || (int) $stats->total === 0) {
            return 'La tienda todavia no tiene productos cargados en el catalogo.';
        }

        $lines = [
            'Productos en el catalogo: ' . (int) $stats->total
                . ' (' . (int) $stats->activos . ' activos, ' . (int) $stats->agotados . ' sin stock).',
            'Rango de precios: $' . number_format((float) $stats->precio_min, 0, ',', '.')
                . ' a $' . number_format((float) $stats->precio_max, 0, ',', '.') . '.',
        ];

        $categories = DB::table('categories')
            ->whereIn('id', Product::query()->where('store_id', $storeId)->select('category_id'))
            ->orderBy('name')
            ->limit(25)
            ->pluck('name');

        if ($categories->isNotEmpty()) {
            $lines[] = 'Categorias: ' . $categories->implode(', ') . '.';
        }

        return implode("\n", $lines);
    }

    /**
     * Busca productos de la tienda por palabras clave de la pregunta,
     * cruzando nombre, descripcion, sku y motos compatibles. Si no encuentra
     * nada especifico, devuelve parte del catalogo para que Claude igual
     * pueda orientar al cliente (sin inventar).
     *
     * @return Collection<int, Product>
     */
    private function findStoreProducts(int $storeId, string $question): Collection
    {
        $tokens = $this->keywords($question);

        $query = Product::query()
            ->where('store_id', $storeId)
            ->with(['motorcycleModels', 'category']);

        if ($tokens->isNotEmpty()) {
            $query->where(function ($outer) use ($tokens): void {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';
                    $outer->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(sku, \'\')) LIKE ?', [$like])
                        ->orWhereHas('motorcycleModels', function ($m) use ($like): void {
                            $m->whereRaw('LOWER(brand) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(model) LIKE ?', [$like]);
                        });
                }
            });
        }

        $products = $query->limit(self::MAX_PRODUCTS)->get();

        // Sin coincidencias: dar algo del catalogo para responder "que tienes".
        if ($products->isEmpty()) {
            $products = Product::query()
                ->where('store_id', $storeId)
                ->with(['motorcycleModels', 'category'])
                ->latest()
                ->limit(self::MAX_PRODUCTS)
                ->get();
        }

        return $products;
    }

    /**
     * Compatibilidad verificada (tabla parts_compatibility), que NO depende de la
     * tienda. Se reutiliza PartsAssistantService en vez de duplicar la deteccion de
     * marca/modelo/anio/tipo, que ya maneja errores de tipeo y palabras protegidas.
     *
     * @return array<string, mixed>
     */
    private function verifiedCompatibility(string $question, int $storeId): array
    {
        $result = $this->parts->search($question, $storeId);

        return [
            'interpretacion'    => $result['interpretacion'],
            'alcance'           => $result['alcance'],
            'aviso'             => $result['aviso'],
            'compatibilidades'  => array_slice($result['compatibilidades'], 0, self::MAX_COMPATIBILITIES),
        ];
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  array<string, mixed>  $verified
     */
    private function buildUserContent(string $question, int $storeId, Collection $products, array $verified): string
    {
        $blocks = [
            "PREGUNTA DEL CLIENTE:\n{$question}",
            "RESUMEN DEL CATALOGO:\n" . $this->catalogSummary($storeId),
        ];

        if ($products->isNotEmpty()) {
            $blocks[] = "PRODUCTOS DE ESTA TIENDA (inventario real):\n" . $this->formatProducts($products);
        }

        $compatBlock = $this->formatCompatibilities($verified);
        if ($compatBlock !== null) {
            $blocks[] = $compatBlock;
        }

        $intercambio = $this->formatInterchange($verified);
        if ($intercambio !== null) {
            $blocks[] = $intercambio;
        }

        return implode("\n\n", $blocks);
    }

    /** @param  Collection<int, Product>  $products */
    private function formatProducts(Collection $products): string
    {
        return $products->map(function (Product $p): string {
            $stock = (int) $p->stock;
            $line  = '- ' . $p->name
                . ' | precio: $' . number_format((float) $p->price, 0, ',', '.')
                . ' | ' . ($stock > 0 ? "stock: {$stock}" : 'SIN STOCK');

            if (! empty($p->sku)) {
                $line .= ' | sku: ' . $p->sku;
            }

            $categoryName = $p->category?->name;
            if (is_string($categoryName) && $categoryName !== '') {
                $line .= ' | categoria: ' . $categoryName;
            }

            $motos = $p->motorcycleModels
                ->map(fn ($m): string => trim("{$m->brand} {$m->model} ({$m->year_from}-" . ($m->year_to ?: 'actual') . ')'))
                ->implode('; ');
            if ($motos !== '') {
                $line .= ' | compatible con: ' . $motos;
            }

            $desc = trim((string) ($p->description ?? ''));
            if ($desc !== '') {
                $line .= ' | ' . mb_substr((string) preg_replace('/\s+/', ' ', $desc), 0, 160);
            }

            return $line;
        })->implode("\n");
    }

    /**
     * Se marca explicitamente que esto NO es inventario: son referencias de un
     * catalogo de compatibilidad. Sin esa aclaracion Claude las ofrece como si la
     * tienda las tuviera, que es exactamente el error que hay que evitar.
     *
     * @param  array<string, mixed>  $verified
     */
    private function formatCompatibilities(array $verified): ?string
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $verified['compatibilidades'];

        if ($rows === []) {
            return null;
        }

        $lines = array_map(static function (array $row): string {
            $line = '- ' . $row['referencia'] . ' (' . $row['tipo_label'] . ')'
                . ' | moto: ' . $row['moto'] . ' ' . $row['anios'];

            if (! empty($row['marca'])) {
                $line .= ' | marca: ' . $row['marca'];
            }

            $inv = $row['en_inventario'];
            $line .= is_array($inv)
                ? ' | EN ESTA TIENDA: ' . $inv['nombre']
                    . ' $' . number_format((float) $inv['precio'], 0, ',', '.')
                    . ($inv['disponible'] ? " (stock {$inv['stock']})" : ' (sin stock)')
                : ' | no esta en el inventario de esta tienda';

            return $line;
        }, $rows);

        $header = "COMPATIBILIDAD VERIFICADA (catalogo de referencia, NO es el inventario de la tienda):";

        $aviso = $verified['aviso'];
        if (is_string($aviso) && $aviso !== '') {
            $header .= "\nAviso de alcance: {$aviso}";
        }

        return $header . "\n" . implode("\n", $lines);
    }

    /**
     * Que OTRAS motos usan las mismas referencias, que es la pregunta real del
     * mostrador: "esto de la NKD le sirve a la Discover?".
     *
     * Sin este bloque el asistente recibia solo las filas de la moto preguntada. Al
     * pedirle "que otras motos usan esta bujia" no tenia con que responder y llenaba
     * el hueco de memoria: invento Suzuki Best, AKT Dynamic y Kymco Agility, que no
     * existen en la tabla, y omitio siete motos que si estaban. Un repuesto mal
     * recomendado se traduce en una devolucion o en un freno que no frena.
     *
     * @param  array<string, mixed>  $verified
     */
    private function formatInterchange(array $verified): ?string
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $verified['compatibilidades'];

        $referencias = collect($rows)->pluck('referencia')->filter()->unique();

        if ($referencias->isEmpty()) {
            return null;
        }

        $motosPorReferencia = DB::table('parts_compatibility')
            ->whereIn('part_reference', $referencias->all())
            ->select('part_reference', 'motorcycle_brand', 'motorcycle_model', 'year_from', 'year_to')
            ->get()
            ->groupBy('part_reference');

        $lines = [];

        foreach ($motosPorReferencia as $referencia => $motos) {
            $nombres = $motos
                ->map(fn ($m): string => trim($m->motorcycle_brand . ' ' . $m->motorcycle_model))
                ->unique()
                ->values();

            // Una sola moto no es intercambiabilidad: no aporta y gasta contexto.
            if ($nombres->count() < 2) {
                continue;
            }

            $lines[] = '- ' . $referencia . ' sirve para: ' . $nombres->implode('; ');
        }

        if ($lines === []) {
            return null;
        }

        return "INTERCAMBIABILIDAD VERIFICADA (lista COMPLETA de motos por referencia;\n"
            . "si una moto NO aparece aca, NO afirmes que esa referencia le sirve):\n"
            . implode("\n", $lines);
    }

    private function systemPrompt(Store $store): string
    {
        $description = trim((string) ($store->description ?? ''));
        $about = $description !== '' ? " Sobre la tienda: {$description}" : '';

        return <<<PROMPT
        Eres el asistente de ventas de la tienda "{$store->name}", que vende repuestos de motos en Colombia.{$about}

        Ayudas a resolver CUALQUIER duda del cliente: que productos hay, precios, stock, compatibilidad con su moto y consejos de repuestos.

        Reglas:
        - Habla en espanol colombiano, claro y cercano.
        - Responde SOLO con los datos que te doy (catalogo e info de compatibilidad). Puedes dar consejo general de mecanica, pero NO inventes referencias, precios ni existencias.
        - Los productos de la tienda que menciones se le muestran al cliente aparte, en una lista con precio y existencias. NO los repitas uno por uno con sus precios en tu respuesta: di cuales le sirven y por que, y deja los numeros para la lista.
        - Si algo no esta en el catalogo, dilo con honestidad y sugiere escribirle al vendedor.
        - Si el dato viene de "compatibilidad verificada" y no del inventario, aclaralo.
        - Se breve y directo.

        REGLA CRITICA sobre que repuesto le sirve a que moto:
        - NUNCA nombres una moto o una referencia que no aparezca literalmente en los datos que te doy. Ni para afirmar que sirve, ni para afirmar que NO sirve.
        - Si te preguntan si un repuesto de una moto le sirve a otra y esa combinacion no esta en los datos, di que no lo tienes verificado y que consulte al vendedor. NO deduzcas, NO compares medidas de memoria, NO digas "usan mordazas distintas" ni razonamientos parecidos.
        - Recomendar mal un repuesto de freno, suspension o direccion es peligroso. Ante la duda, di que no esta verificado.
        - Tu memoria de mecanica sirve para consejos de mantenimiento (cada cuanto cambiar algo, sintomas de desgaste), NO para afirmar compatibilidades.
        PROMPT;
    }

    // ── Historial ────────────────────────────────────────────────────────

    /**
     * Anthropic exige que el primer mensaje sea del usuario. El chat del panel
     * arranca con un saludo del asistente, asi que si se reenvia tal cual la API
     * responde 400: hay que descartar los turnos de assistant que van al inicio.
     * Gemini es mas tolerante, pero la conversacion igual se lee mejor asi.
     *
     * @param  list<array{role: string, content: string}>  $history
     * @return list<array{role: string, content: string}>
     */
    private function normalizeHistory(array $history): array
    {
        $clean = [];

        foreach ($history as $turn) {
            $role    = $turn['role'] ?? null;
            $content = trim((string) ($turn['content'] ?? ''));

            if (! in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            if ($clean === [] && $role === 'assistant') {
                continue;
            }

            $clean[] = ['role' => $role, 'content' => $content];
        }

        return array_values(array_slice($clean, -self::MAX_HISTORY));
    }

    /** @return Collection<int, string> */
    private function keywords(string $question): Collection
    {
        $ascii = strtr(mb_strtolower(trim($question), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        ]);
        $clean = preg_replace('/[^a-z0-9ñ\s]/', ' ', $ascii);

        return collect(preg_split('/\s+/', (string) $clean))
            ->map(fn (string $w): string => trim($w))
            ->filter(fn (string $w): bool => mb_strlen($w) >= 3 && ! in_array($w, self::STOP_WORDS, true))
            ->map(fn (string $w): string => $this->singular($w))
            ->unique()
            ->take(6)
            ->values();
    }

    /**
     * Pasa la palabra a singular para buscar en el catalogo.
     *
     * El cliente pregunta como habla ("que aceites tienes?") pero los productos se
     * cargan en singular ("aceite lubricante cadena"). Como la busqueda es por
     * fragmento, "aceites" no encuentra "aceite" y la tienda respondia que no tenia
     * -- con 14 aceites cargados. Una venta perdida por una letra.
     *
     * Al reves no hace falta: buscar "aceite" ya encuentra "aceites" por ser fragmento.
     *
     * No es un lematizador de espanol, es la regla que cubre el 90% de los casos del
     * mostrador: frenos, pastillas, llantas, espejos, guayas, motores, bujias.
     */
    private function singular(string $word): string
    {
        // "motores" -> "motor", "bujes" -> "buje". Se exige largo para no destrozar
        // palabras cortas ("mes" no debe volverse "m").
        if (mb_strlen($word) >= 6 && str_ends_with($word, 'es')) {
            return mb_substr($word, 0, -2);
        }

        // "aceites" -> "aceite", "frenos" -> "freno". Se protege "-ss" y palabras que
        // ya terminan en "s" siendo singulares cortas.
        if (mb_strlen($word) >= 5 && str_ends_with($word, 's') && ! str_ends_with($word, 'ss')) {
            return mb_substr($word, 0, -1);
        }

        return $word;
    }
}
