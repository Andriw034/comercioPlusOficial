<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MotorcycleModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MotorcycleController extends Controller
{
    public function brands(): JsonResponse
    {
        $brands = MotorcycleModel::select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return response()->json(['data' => $brands]);
    }

    public function models(Request $request): JsonResponse
    {
        $request->validate([
            'brand' => 'required|string',
        ]);

        $models = MotorcycleModel::where('brand', $request->input('brand'))
            ->select('id', 'brand', 'model', 'year_from', 'year_to', 'engine_cc', 'type')
            ->orderBy('model')
            ->get();

        return response()->json(['data' => $models]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = MotorcycleModel::query();

        if ($brand = $request->input('brand')) {
            $query->where('brand', $brand);
        }

        if ($model = $request->input('model')) {
            $query->where('model', 'like', "%{$model}%");
        }

        if ($year = $request->input('year')) {
            $query->where('year_from', '<=', (int) $year)
                ->where(function ($q) use ($year) {
                    $q->whereNull('year_to')->orWhere('year_to', '>=', (int) $year);
                });
        }

        $motos = $query->orderBy('brand')->orderBy('model')->get();

        return response()->json(['data' => $motos]);
    }
}
