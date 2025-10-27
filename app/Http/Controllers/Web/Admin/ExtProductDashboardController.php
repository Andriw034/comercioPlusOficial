<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExternalProductApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;

class ExtProductDashboardController extends Controller
{
    public function __construct(private ExternalProductApi $api) {}

    public function index(Request $request)
    {
        try {
            $page     = (int) $request->query('page', 1);
            $perPage  = (int) $request->query('per_page', 12);

            $data = $this->api->list(
                filters: [
                    'search'      => $request->query('search'),
                    'category_id' => $request->query('category_id'),
                    'sort'        => $request->query('sort'),
                ],
                perPage: $perPage,
                page:    $page
            );

            return view('admin.ext-products.index', [
                'raw'      => $data,
                'products' => $data['products'] ?? ($data['data'] ?? []),
                'total'    => $data['total']   ?? null,
                'limit'    => $data['limit']   ?? $perPage,
                'page'     => $page,
            ]);
        } catch (\Throwable $e) {
            return view('admin.ext-products.index', [
                'raw'      => [],
                'products' => [],
                'total'    => 0,
                'limit'    => $perPage,
                'page'     => $page ?? 1,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function import(Request $request, string $externalId)
    {
        try {
            // Obtener datos del producto externo
            $externalProduct = $this->api->get($externalId);

            // Obtener la tienda del usuario
            $user = auth()->user();
            $store = $user->stores()->first();

            if (!$store) {
                return redirect()->back()->with('error', 'Debes tener una tienda para importar productos.');
            }

            // Verificar si el producto ya existe (por nombre)
            $existingProduct = Product::where('store_id', $store->id)
                ->where('name', $externalProduct['title'] ?? $externalProduct['name'])
                ->first();

            if ($existingProduct) {
                return redirect()->back()->with('warning', 'Este producto ya ha sido importado anteriormente.');
            }

            // Crear o encontrar categoría
            $category = null;
            if (!empty($externalProduct['category'])) {
                $category = Category::firstOrCreate(
                    ['store_id' => $store->id, 'name' => $externalProduct['category']],
                    ['slug' => Str::slug($externalProduct['category'])]
                );
            }

            // Descargar y guardar imagen
            $imagePath = null;
            if (!empty($externalProduct['thumbnail']) || (!empty($externalProduct['images']) && is_array($externalProduct['images']))) {
                $imageUrl = $externalProduct['thumbnail'] ?? $externalProduct['images'][0] ?? null;

                if ($imageUrl) {
                    try {
                        $imageContents = file_get_contents($imageUrl);
                        if ($imageContents) {
                            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = 'products/' . Str::uuid() . '.' . $extension;
                            Storage::disk('public')->put($filename, $imageContents);
                            $imagePath = $filename;
                        }
                    } catch (\Exception $e) {
                        // Si falla la descarga, continuar sin imagen
                    }
                }
            }

            // Crear producto local
            Product::create([
                'store_id'     => $store->id,
                'user_id'      => $user->id,
                'category_id'  => $category?->id,
                'name'         => $externalProduct['title'] ?? $externalProduct['name'],
                'slug'         => Str::slug($externalProduct['title'] ?? $externalProduct['name']),
                'description'  => $externalProduct['description'] ?? null,
                'price'        => $externalProduct['price'] ?? 0,
                'stock'        => $externalProduct['stock'] ?? 0,
                'status'       => 1, // Activo por defecto
                'image_path'   => $imagePath,
            ]);

            return redirect()->back()->with('success', 'Producto importado exitosamente.');

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al importar el producto: ' . $e->getMessage());
        }
    }
}
