<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Asistente conversacional de una tienda, con Claude REAL.
 *
 * A diferencia de PartsAssistantService (busqueda estructurada de compatibilidad),
 * aca se le da a Claude el catalogo REAL de la tienda y responde como si fuera el
 * vendedor de ESA tienda. La llamada a Anthropic se hace desde el backend, no desde
 * el navegador: asi funciona para cualquier cliente en produccion y la API key
 * nunca queda expuesta en el frontend.
 *
 * El contexto que se le arma tiene tres capas, y esa es la razon de que pueda
 * responder "cualquier cosa" sin inventar: un resumen agregado del catalogo (para
 * preguntas tipo "que tienes"), los productos que coinciden con la pregunta, y la
 * tabla de compatibilidad verificada (que no depende de la tienda).
 */
class ClaudeAssistantService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /** Tope de productos y compatibilidades en el contexto: mas es ruido y tokens. */
    private const MAX_PRODUCTS = 15;
    private const MAX_COMPATIBILITIES = 15;

    /** Turnos previos que se le reenvian a Claude para que mantenga el hilo. */
    private const MAX_HISTORY = 10;

    /** Palabras vacias que no aportan a la busqueda de productos. */
    private const STOP_WORDS = [
        'que', 'cual', 'cuales', 'como', 'le', 'les', 'sirve', 'sirven', 'una', 'uno',
        'unos', 'unas', 'para', 'del', 'los', 'las', 'con', 'por', 'tiene', 'tienes',
        'tienen', 'hay', 'moto', 'ano', 'anio', 'año', 'de', 'la', 'el', 'en', 'mi',
        'me', 'un', 'y', 'o', 'a', 'es', 'necesito', 'quiero', 'busco', 'sobre',
    ];

    public function __construct(private readonly PartsAssistantService $parts)
    {
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

        $answer = $this->callClaude(
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
        - Cuando recomiendes un producto, di su precio y si hay stock.
        - Si algo no esta en el catalogo, dilo con honestidad y sugiere escribirle al vendedor.
        - Si el dato viene de "compatibilidad verificada" y no del inventario, aclaralo.
        - Se breve y directo.
        PROMPT;
    }

    // ── Llamada a Anthropic ──────────────────────────────────────────────

    /**
     * Anthropic exige que el primer mensaje sea del usuario. El chat del panel
     * arranca con un saludo del asistente, asi que si se reenvia tal cual la API
     * responde 400: hay que descartar los turnos de assistant que van al inicio.
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

    /**
     * @param  list<array{role: string, content: string}>  $history
     */
    private function callClaude(string $system, array $history, string $userContent): string
    {
        $key = config('services.anthropic.key');

        if (empty($key)) {
            throw new RuntimeException('Falta configurar ANTHROPIC_API_KEY en el .env');
        }

        $messages   = $history;
        $messages[] = ['role' => 'user', 'content' => $userContent];

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
            'content-type'      => 'application/json',
        ])->timeout(60)->retry(1, 500, throw: false)->post(self::ENDPOINT, [
            'model'      => config('services.anthropic.model'),
            'max_tokens' => config('services.anthropic.max_tokens', 900),
            'system'     => $system,
            'messages'   => $messages,
        ]);

        if (! $response->successful()) {
            // El body trae el motivo real (modelo retirado, credito agotado, etc.).
            // Sin esto en el log el fallo es indistinguible desde afuera.
            Log::warning('Anthropic API error', [
                'status' => $response->status(),
                'model'  => config('services.anthropic.model'),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('El asistente no esta disponible en este momento.');
        }

        $text = $response->json('content.0.text');

        return is_string($text) && $text !== ''
            ? $text
            : 'No pude generar una respuesta. Intenta reformular tu pregunta.';
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
            ->unique()
            ->take(6)
            ->values();
    }
}
