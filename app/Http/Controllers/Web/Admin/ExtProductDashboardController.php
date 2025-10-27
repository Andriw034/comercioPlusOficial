<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExternalProductApi;
use Illuminate\Http\Request;

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
}
