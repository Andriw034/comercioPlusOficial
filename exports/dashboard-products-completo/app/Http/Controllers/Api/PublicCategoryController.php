<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicCategoryController extends Controller
{
    /**
     * Display a listing of categories for public access.
     */
    public function index()
    {
        $categories = Category::with('products', 'parent', 'children')->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }
}
