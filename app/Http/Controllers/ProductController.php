<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'image']);

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(6);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name', // <-- REGLA AGREGADA AQUÍ
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048', 
        ], [
            'category_id.required' => 'Por favor, selecciona una categoría para el equipo o componente.',
            'name.required' => 'El nombre del artículo es obligatorio.',
            'name.unique' => 'Ya existe un producto registrado con este nombre. Por favor, sé más específico.',
            'description.required' => 'Debes incluir una descripción técnica.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'stock.required' => 'Ingresa el stock inicial disponible.',
            'image.image' => 'El archivo debe ser una imagen válida (jpeg, png, bmp, gif, svg, o webp).',
            'image.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($request->name);

        $product = Product::create($validated);

        if ($request->hasFile('image')) {
            $ruta = $request->file('image')->store('productos', 'public');
            $product->image()->create(['url' => $ruta]);
        }

        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            // <-- REGLA AGREGADA AQUÍ (ignora el ID actual)
            'name' => 'required|string|max:255|unique:products,name,' . $product->id, 
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ], [
            'category_id.required' => 'Por favor, selecciona una categoría para el equipo o componente.',
            'name.required' => 'El nombre del artículo es obligatorio.',
            'name.unique' => 'Ya existe un producto registrado con este nombre. Por favor, elige otro o edita el existente.',
            'description.required' => 'Debes incluir una descripción técnica.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'stock.required' => 'Ingresa el stock disponible.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($request->name);

        $product->update($validated);

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image->url);
                $product->image()->delete();
            }
            
            // Guardar la nueva imagen
            $ruta = $request->file('image')->store('productos', 'public');
            $product->image()->create(['url' => $ruta]);
        }

        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index');
    }
}