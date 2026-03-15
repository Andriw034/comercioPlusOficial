<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicCategoryController extends Controller
{
    /**
     * Display a listing of categories for public access.
     */
    public function index()
    {
        $categories = Cache::remember('public_categories_list', 300, function () {
            return Category::with('products:id,name,slug,price,image,image_url,category_id', 'parent:id,name,slug', 'children:id,name,slug,parent_id')->get();
        });

        return response()->json([
            'status' => 'ok',
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }
}
