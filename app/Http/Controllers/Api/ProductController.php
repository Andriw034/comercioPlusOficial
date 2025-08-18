<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Product::included()
            ->filter()
            ->sort()
            ->getOrPaginate();

        return response()->json([
            'status' => 'ok',
            'message' => 'Listado de productos',
            'data' => $productos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image'       => 'nullable|image|max:2048',
        ]);

        $store = Store::where('user_id', Auth::id())->first();

        if (!$store) {
            return redirect()->back()->with('error', 'Debes tener una tienda para agregar productos.');
        }

        $product = new Product();
        $product->name        = $request->name;
        $product->price       = $request->price;
        $product->description = $request->description;
        $product->store_id    = $store->id;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('productos', 'public');
            $product->image = $imagePath;
        }

        $product->save();

        return redirect()->route('producto.create')->with('success', 'Producto creado correctamente.');
    }

    }
