<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicProductController extends Controller
{
    /**
     * Display a listing of products for public access.
     */
    public function index(Request $request)
    {
        // Implementación con validación y paginación personalizada
        $data = $request->validate([
            'q'                => ['nullable','string','max:100'],
            'category_id'      => ['nullable','integer'],
            'offer'            => ['nullable','in:0,1'],
            'min_price'        => ['nullable','numeric','min:0'],
            'max_price'        => ['nullable','numeric','min:0'],
            'sort'             => ['nullable','in:price_asc,price_desc,rating_desc,newest'],
            'page'             => ['nullable','integer','min:1'],
            'per_page'         => ['nullable','integer','min:1','max:50'],
            'motorcycle_brand' => ['nullable','string','max:50'],
            'motorcycle_model' => ['nullable','string','max:80'],
            'motorcycle_year'  => ['nullable','integer','min:1990','max:2040'],
        ]);

        $perPage = $data['per_page'] ?? 12;

        $cacheVersion = Cache::get('public_products_version', 1);
        $cacheParams = array_filter([
            'q'                => $data['q'] ?? null,
            'category_id'      => $data['category_id'] ?? null,
            'offer'            => $data['offer'] ?? null,
            'min_price'        => $data['min_price'] ?? null,
            'max_price'        => $data['max_price'] ?? null,
            'sort'             => $data['sort'] ?? null,
            'page'             => $data['page'] ?? 1,
            'per_page'         => $perPage,
            'motorcycle_brand' => $data['motorcycle_brand'] ?? null,
            'motorcycle_model' => $data['motorcycle_model'] ?? null,
            'motorcycle_year'  => $data['motorcycle_year'] ?? null,
            'v'                => $cacheVersion,
        ], fn ($v) => $v !== null);
        ksort($cacheParams);
        $cacheKey = 'public_products_' . md5(serialize($cacheParams));

        $result = Cache::remember($cacheKey, 300, function () use ($data, $perPage) {
            $q = Product::query()
                ->select([
                    'id','name','slug','description','image','image_url','price','stock',
                    'category_id','offer','average_rating','store_id','created_at'
                ])
                ->with('category:id,name')
                ->where('stock', '>', 0); // Only show products with stock

            if (!empty($data['q'])) {
                $term = $data['q'];
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                       ->orWhere('description', 'like', "%{$term}%");
                });
            }

            if (!empty($data['category_id'])) {
                $q->where('category_id', (int)$data['category_id']);
            }

            if (isset($data['offer'])) {
                $q->where('offer', (int)$data['offer'] === 1);
            }

            if (!empty($data['min_price'])) {
                $q->where('price', '>=', (float)$data['min_price']);
            }

            if (!empty($data['max_price'])) {
                $q->where('price', '<=', (float)$data['max_price']);
            }

            // Motorcycle compatibility filters
            $hasMotoFilter = !empty($data['motorcycle_brand']) || !empty($data['motorcycle_model']) || !empty($data['motorcycle_year']);
            if ($hasMotoFilter) {
                $q->whereHas('motorcycleModels', function ($mq) use ($data) {
                    if (!empty($data['motorcycle_brand'])) {
                        $mq->where('brand', $data['motorcycle_brand']);
                    }
                    if (!empty($data['motorcycle_model'])) {
                        $mq->where('model', 'like', '%' . $data['motorcycle_model'] . '%');
                    }
                    if (!empty($data['motorcycle_year'])) {
                        $year = (int) $data['motorcycle_year'];
                        $mq->where('year_from', '<=', $year)
                            ->where(function ($yq) use ($year) {
                                $yq->whereNull('year_to')->orWhere('year_to', '>=', $year);
                            });
                    }
                });
            }

            switch ($data['sort'] ?? null) {
                case 'price_asc':  $q->orderBy('price', 'asc'); break;
                case 'price_desc': $q->orderBy('price', 'desc'); break;
                case 'rating_desc':$q->orderBy('average_rating', 'desc'); break;
                case 'newest':     $q->orderBy('created_at', 'desc'); break;
                default:           $q->orderBy('name'); break;
            }

            $page = $q->paginate($perPage)->withQueryString();

            return [
                'data' => $page->items(),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'per_page'     => $page->perPage(),
                    'total'        => $page->total(),
                    'last_page'    => $page->lastPage(),
                ],
            ];
        });

        return response()->json($result);
    }
}
