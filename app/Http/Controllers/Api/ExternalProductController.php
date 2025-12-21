<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalProductApi;
use Illuminate\Http\Request;

class ExternalProductController extends Controller
{
    public function __construct(private ExternalProductApi $api) {}

    public function index(Request $r)
    {
        $data = $this->api->list(
            filters: [
                'search'      => $r->query('search'),
                'category_id' => $r->query('category_id'),
                'sort'        => $r->query('sort'),
            ],
            perPage: (int) $r->query('per_page', 12),
            page:    (int) $r->query('page', 1),
        );

        return response()->json($data);
    }

    public function show(string $externalId)
    {
        return response()->json($this->api->get($externalId));
    }

    public function store(Request $r)
    {
        $payload = $r->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'brand'       => 'nullable|string|max:255',
            'category_id' => 'nullable|string|max:255',
        ]);

        $created = $this->api->create($payload);

        return response()->json([
            'message' => 'Producto creado en la API externa',
            'data'    => $created,
        ], 201);
    }

    public function update(Request $r, string $externalId)
    {
        $payload = $r->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'brand'       => 'sometimes|nullable|string|max:255',
            'category_id' => 'sometimes|nullable|string|max:255',
        ]);

        $updated = $this->api->update($externalId, $payload);

        return response()->json([
            'message' => 'Producto actualizado en la API externa',
            'data'    => $updated,
        ]);
    }

    public function destroy(string $externalId)
    {
        $res = $this->api->delete($externalId);

        return response()->json([
            'message'      => 'Producto eliminado en la API externa',
            'external_id'  => $externalId,
            'provider_res' => $res,
        ]);
    }
}
