<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;
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
 */
class ClaudeAssistantService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    /** Palabras vacias que no aportan a la busqueda de productos. */
    private const STOP_WORDS = [
        'que', 'cual', 'cuales', 'como', 'le', 'les', 'sirve', 'sirven', 'una', 'uno',
        'unos', 'unas', 'para', 'del', 'los', 'las', 'con', 'por', 'tiene', 'tienes',
        'tienen', 'hay', 'moto', 'ano', 'anio', 'año', 'de', 'la', 'el', 'en', 'mi',
        'me', 'un', 'y', 'o', 'a', 'es', 'necesito', 'quiero', 'busco', 'sobre',
    ];

    public function ask(string $question, int $storeId): array
    {
        $store = Store::query()->find($storeId);

        if ($store === null) {
            throw new RuntimeException("No existe la tienda {$storeId}");
        }

        $products = $this->findStoreProducts($store->id, $question);

        $context = $this->buildContext($products);
        $answer  = $this->callClaude($question, $store->name, $context);

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

    /**
     * Busca productos de la tienda por palabras clave de la pregunta,
     * cruzando nombre, descripcion y motos compatibles. Si no encuentra
     * nada especifico, devuelve parte del catalogo para que Claude igual
     * pueda orientar al cliente (sin inventar).
     */
    private function findStoreProducts(int $storeId, string $question): Collection
    {
        $tokens = $this->keywords($question);

        $query = Product::query()
            ->where('store_id', $storeId)
            ->with('motorcycleModels');

        if ($tokens->isNotEmpty()) {
            $query->where(function ($outer) use ($tokens): void {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';
                    $outer->orWhereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(description, "")) LIKE ?', [$like])
                        ->orWhereHas('motorcycleModels', function ($m) use ($like): void {
                            $m->whereRaw('LOWER(brand) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(model) LIKE ?', [$like]);
                        });
                }
            });
        }

        $products = $query->limit(15)->get();

        // Sin coincidencias: dar algo del catalogo para responder "que tienes".
        if ($products->isEmpty()) {
            $products = Product::query()
                ->where('store_id', $storeId)
                ->with('motorcycleModels')
                ->latest()
                ->limit(20)
                ->get();
        }

        return $products;
    }

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

    private function buildContext(Collection $products): string
    {
        if ($products->isEmpty()) {
            return 'La tienda todavia no tiene productos cargados en el catalogo.';
        }

        return $products->map(function (Product $p): string {
            $stock = (int) $p->stock;
            $disp  = $stock > 0 ? "stock: {$stock}" : 'SIN STOCK';
            $line  = "- {$p->name} | precio: \${$p->price} | {$disp}";

            $motos = $p->motorcycleModels
                ->map(fn ($m): string => trim("{$m->brand} {$m->model} ({$m->year_from}-" . ($m->year_to ?: 'actual') . ')'))
                ->implode('; ');
            if ($motos !== '') {
                $line .= " | compatible con: {$motos}";
            }

            $desc = trim((string) ($p->description ?? ''));
            if ($desc !== '') {
                $line .= ' | ' . mb_substr(preg_replace('/\s+/', ' ', $desc), 0, 160);
            }

            return $line;
        })->implode("\n");
    }

    private function callClaude(string $question, string $storeName, string $context): string
    {
        $key = config('services.anthropic.key');

        if (empty($key)) {
            throw new RuntimeException('Falta configurar ANTHROPIC_API_KEY en el .env');
        }

        $prompt = <<<PROMPT
        Eres el asistente de ventas de la tienda "{$storeName}", que vende repuestos de motos en Colombia.

        Atiendes a clientes que quieren comprar en ESTA tienda. Responde SOLO con lo que hay en el catalogo que te paso abajo.

        IMPORTANTE:
        - Usa lenguaje colombiano natural y cercano.
        - Recomienda productos de esta tienda, con su precio.
        - Si hay varias opciones compatibles, menciona TODAS.
        - Explica POR QUE ese repuesto le sirve a la moto del cliente.
        - Si un producto no tiene stock, avisalo.
        - Si NO hay nada en el catalogo para lo que pide, dilo con honestidad y sugiere que le escriba al vendedor. NO inventes productos ni referencias.

        PREGUNTA DEL CLIENTE:
        {$question}

        CATALOGO DE LA TIENDA:
        {$context}

        RESPONDE de forma clara y util:
        PROMPT;

        $response = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => config('services.anthropic.version', '2023-06-01'),
            'content-type'      => 'application/json',
        ])->timeout(60)->post(self::ENDPOINT, [
            'model'      => config('services.anthropic.model', 'claude-sonnet-4-20250514'),
            'max_tokens' => config('services.anthropic.max_tokens', 800),
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (! $response->successful()) {
            Log::warning('Anthropic API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('El asistente no esta disponible en este momento.');
        }

        $text = $response->json('content.0.text');

        return is_string($text) && $text !== ''
            ? $text
            : 'No pude generar una respuesta. Intenta reformular tu pregunta.';
    }
}
