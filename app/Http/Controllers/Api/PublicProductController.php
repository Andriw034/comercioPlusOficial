<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    /**
     * Display a listing of products for public access.
     */
    public function index(Request $request)
    {
        // Implementación con validación y paginación personalizada
        $data = $request->validate([
            'q'           => ['nullable','string','max:100'],
            'category_id' => ['nullable','integer'],
            'offer'       => ['nullable','in:0,1'],
            'min_price'   => ['nullable','numeric','min:0'],
            'max_price'   => ['nullable','numeric','min:0'],
            'sort'        => ['nullable','in:price_asc,price_desc,rating_desc,newest'],
            'page'        => ['nullable','integer','min:1'],
            'per_page'    => ['nullable','integer','min:1','max:50'],
        ]);

        $perPage = $data['per_page'] ?? 12;

        $q = Product::query()
            ->select([
                'id','name','slug','description','image as image','price','stock',
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

        switch ($data['sort'] ?? null) {
            case 'price_asc':  $q->orderBy('price', 'asc'); break;
            case 'price_desc': $q->orderBy('price', 'desc'); break;
            case 'rating_desc':$q->orderBy('average_rating', 'desc'); break;
            case 'newest':     $q->orderBy('created_at', 'desc'); break;
            default:           $q->orderBy('name'); break;
        }

        $page = $q->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'last_page'    => $page->lastPage(),
            ],
        ]);
    }
}
