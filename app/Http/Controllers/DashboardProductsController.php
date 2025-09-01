<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\PlaceholderData;
use Illuminate\Support\Facades\Storage;

class DashboardProductsController extends Controller
{
    public function index()
    {
        $products = collect(session('products_added', []))
            ->concat(PlaceholderData::products());

        return view('dashboard.products.index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return view('dashboard.products.create', [
            'categories' => PlaceholderData::categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','min:3','max:255'],
            'description' => ['nullable','string','max:1000'],
            'price'       => ['required','numeric','min:0'],
            'stock'       => ['required','integer','min:0'],
            'category_id' => ['required','string'],
            'image'       => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            try {
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $imageUrl = asset('storage/'.$imagePath);
            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Error al subir la imagen: '.$e->getMessage()])->withInput();
            }
        }

        $added = collect(session('products_added', []))->all();

        $categories = collect(PlaceholderData::categories());
        $category   = $categories->firstWhere('id', $data['category_id']);
        $categoryName = $category ? $category['name'] : 'Sin categoría';

        $new = [
            'id'          => (string) (count($added) + 1000),
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => (float) $data['price'],
            'stock'       => (int) $data['stock'],
            'category'    => $categoryName,
            'category_id' => $data['category_id'],
            'image'       => $imageUrl ?? 'https://picsum.photos/400/400?random=' . rand(9, 9999),
            'created_at'  => now()->toDateTimeString(),
        ];

        session(['products_added' => array_values([$new, ...$added])]);

        return redirect()->route('dashboard.products.index')
            ->with('ok', 'Producto "'.$data['name'].'" creado correctamente.');
    }

    public function edit($id)
    {
        $added   = collect(session('products_added', []));
        $product = $added->firstWhere('id', $id);

        if (!$product) {
            abort(404, 'Producto no encontrado');
        }

        return view('dashboard.products.edit', [
            'product'    => $product,
            'categories' => PlaceholderData::categories(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => ['required','string','min:3','max:255'],
            'description' => ['nullable','string','max:1000'],
            'price'       => ['required','numeric','min:0'],
            'stock'       => ['required','integer','min:0'],
            'category_id' => ['required','string'],
            'image'       => ['nullable','image','mimes:jpeg,png,jpg,gif','max:10240'],
        ]);

        $added   = collect(session('products_added', []));
        $product = $added->firstWhere('id', $id);
        if (!$product) abort(404, 'Producto no encontrado');

        $imageUrl = $product['image'];

        if ($request->hasFile('image')) {
            try {
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $imageUrl = asset('storage/'.$imagePath);
            } catch (\Exception $e) {
                return back()->withErrors(['image' => 'Error al subir la imagen: '.$e->getMessage()])->withInput();
            }
        }

        $categories   = collect(PlaceholderData::categories());
        $category     = $categories->firstWhere('id', $data['category_id']);
        $categoryName = $category ? $category['name'] : 'Sin categoría';

        $updated = [
            'id'          => $id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => (float) $data['price'],
            'stock'       => (int) $data['stock'],
            'category'    => $categoryName,
            'category_id' => $data['category_id'],
            'image'       => $imageUrl,
            'created_at'  => $product['created_at'],
        ];

        $updatedAdded = $added->map(fn($p) => $p['id'] == $id ? $updated : $p)->values()->all();
        session(['products_added' => $updatedAdded]);

        return redirect()->route('dashboard.products.index')
            ->with('ok', 'Producto "'.$data['name'].'" actualizado correctamente.');
    }

    public function destroy($id)
    {
        $added   = collect(session('products_added', []));
        $product = $added->firstWhere('id', $id);
        if (!$product) abort(404, 'Producto no encontrado');

        $updatedAdded = $added->reject(fn($p) => $p['id'] == $id)->values()->all();
        session(['products_added' => $updatedAdded]);

        return redirect()->route('dashboard.products.index')
            ->with('ok', 'Producto "'.$product['name'].'" eliminado correctamente.');
    }
}
