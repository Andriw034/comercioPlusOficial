<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'], // jpg, png, webp…
            'slug'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'store_id'    => ['nullable', 'integer'],
            'user_id'     => ['nullable', 'integer'],
            'status'      => ['nullable', 'string'],
            'offer'       => ['nullable', 'numeric'],
            'average_rating' => ['nullable', 'numeric'],
        ]);

        $product = new Product($data);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public'); // "products/archivo.jpg"
            $product->image_path = $path;
        }

        $product->save();

        return redirect()->route('products.index')->with('status', 'Producto creado.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'slug'        => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'store_id'    => ['nullable', 'integer'],
            'user_id'     => ['nullable', 'integer'],
            'status'      => ['nullable', 'string'],
            'offer'       => ['nullable', 'numeric'],
            'average_rating' => ['nullable', 'numeric'],
        ]);

        $product->fill($data);

        if ($request->hasFile('image')) {
            // borrar anterior si existe
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $path = $request->file('image')->store('products', 'public');
            $product->image_path = $path;
        }

        $product->save();

        return redirect()->route('products.edit', $product)->with('status', 'Producto actualizado.');
    }
}
