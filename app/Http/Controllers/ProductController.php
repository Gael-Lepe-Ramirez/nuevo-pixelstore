<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /** Opciones de orden del catálogo: valor del filtro => [columna, dirección] */
    public const SORTS = [
        'recientes' => ['created_at', 'desc'],
        'precio_asc' => ['price', 'asc'],
        'precio_desc' => ['price', 'desc'],
        'nombre' => ['name', 'asc'],
    ];

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'image']);

        // Los clientes solo ven productos activos; el administrador ve también los desactivados.
        if (! $request->user()?->can('admin')) {
            $query->where('is_active', true);
        }

        // Búsqueda por nombre o descripción (agrupada para no mezclarse con los demás filtros)
        if ($request->filled('search')) {
            $texto = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $texto)->orWhere('description', 'like', $texto));
        }

        // Filtros por categoría y marca: SELECT ... WHERE category_id = ? AND brand_id = ?
        if ($request->filled('categoria')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->categoria));
        }
        if ($request->filled('marca')) {
            $query->where('brand_id', $request->integer('marca'));
        }

        // Orden: SELECT ... ORDER BY price ASC|DESC, name o created_at
        [$columna, $direccion] = self::SORTS[$request->input('orden')] ?? self::SORTS['recientes'];
        $query->orderBy($columna, $direccion)->orderBy('id');

        $products = $query->paginate(9)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Detalle de un producto, con productos relacionados de la misma categoría.
     */
    public function show(Request $request, Product $product)
    {
        // Un producto desactivado solo lo puede ver el administrador.
        abort_if(! $product->is_active && ! $request->user()?->can('admin'), 404);

        $product->load(['category', 'brand', 'image']);

        $relacionados = Product::with(['brand', 'image'])
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('products.show', compact('product', 'relacionados'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::orderBy('name')->get();
        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255|unique:products,name', // <-- REGLA AGREGADA AQUÍ
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|max:2048', 
        ], [
            'category_id.required' => 'Por favor, selecciona una categoría para el equipo o componente.',
            'brand_id.required' => 'Selecciona la marca del producto.',
            'name.required' => 'El nombre del artículo es obligatorio.',
            'name.unique' => 'Ya existe un producto registrado con este nombre. Por favor, sé más específico.',
            'description.required' => 'Debes incluir una descripción técnica.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
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
        $brands = Brand::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            // <-- REGLA AGREGADA AQUÍ (ignora el ID actual)
            'name' => 'required|string|max:255|unique:products,name,' . $product->id, 
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|max:2048',
        ], [
            'category_id.required' => 'Por favor, selecciona una categoría para el equipo o componente.',
            'brand_id.required' => 'Selecciona la marca del producto.',
            'name.required' => 'El nombre del artículo es obligatorio.',
            'name.unique' => 'Ya existe un producto registrado con este nombre. Por favor, elige otro o edita el existente.',
            'description.required' => 'Debes incluir una descripción técnica.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
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