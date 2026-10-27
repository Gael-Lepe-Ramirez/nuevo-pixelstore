<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Catálogos del modelo de datos (solo admin): MARCA, CATEGORIA, METODO_ENVIO y METODO_PAGO.
 * Un solo controlador, guiado por la configuración de CATALOGS, para no repetir cuatro CRUD iguales.
 */
class CatalogController extends Controller
{
    /**
     * 'usage' = [tabla, columna] que referencia al catálogo. Con ON DELETE RESTRICT (RN6) no se puede
     * borrar un registro en uso, así que se avisa antes de intentarlo (se cuentan también los productos
     * eliminados lógicamente, porque siguen conservando su llave foránea).
     */
    public const CATALOGS = [
        'brands' => [
            'model' => Brand::class, 'table' => 'brands', 'title' => 'Marcas', 'singular' => 'marca',
            'usage' => ['products', 'brand_id'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'max' => 50, 'required' => true, 'unique' => true],
                ['name' => 'country', 'label' => 'País', 'type' => 'text', 'max' => 50, 'required' => true],
                ['name' => 'website', 'label' => 'Sitio web', 'type' => 'url', 'max' => 100, 'required' => false],
            ],
        ],
        'categories' => [
            'model' => Category::class, 'table' => 'categories', 'title' => 'Categorías', 'singular' => 'categoría',
            'usage' => ['products', 'category_id'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'max' => 100, 'required' => true, 'unique' => true],
            ],
        ],
        'shipping-methods' => [
            'model' => ShippingMethod::class, 'table' => 'shipping_methods', 'title' => 'Métodos de envío', 'singular' => 'método de envío',
            'usage' => ['orders', 'shipping_method_id'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'max' => 50, 'required' => true, 'unique' => true],
                ['name' => 'cost', 'label' => 'Costo ($)', 'type' => 'money', 'required' => true],
                ['name' => 'estimated_days', 'label' => 'Días estimados', 'type' => 'days', 'required' => true],
            ],
        ],
        'payment-methods' => [
            'model' => PaymentMethod::class, 'table' => 'payment_methods', 'title' => 'Métodos de pago', 'singular' => 'método de pago',
            'usage' => ['orders', 'payment_method_id'],
            'fields' => [
                ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'max' => 50, 'required' => true, 'unique' => true],
            ],
        ],
    ];

    public function index(string $catalog)
    {
        $cfg = $this->config($catalog);
        $rows = $cfg['model']::orderBy('name')->get();

        $usage = [];
        foreach ($rows as $row) {
            $usage[$row->id] = $this->inUse($cfg, $row->id);
        }

        return view('catalogs.index', compact('catalog', 'cfg', 'rows', 'usage'));
    }

    public function create(string $catalog)
    {
        $cfg = $this->config($catalog);

        return view('catalogs.form', ['catalog' => $catalog, 'cfg' => $cfg, 'row' => null]);
    }

    public function store(Request $request, string $catalog)
    {
        $cfg = $this->config($catalog);
        $cfg['model']::create($this->validated($request, $catalog, $cfg));

        return redirect()->route('catalogs.index', $catalog)->with('success', ucfirst($cfg['singular']).' registrada correctamente.');
    }

    public function edit(string $catalog, int $id)
    {
        $cfg = $this->config($catalog);
        $row = $cfg['model']::findOrFail($id);

        return view('catalogs.form', compact('catalog', 'cfg', 'row'));
    }

    public function update(Request $request, string $catalog, int $id)
    {
        $cfg = $this->config($catalog);
        $row = $cfg['model']::findOrFail($id);
        $row->update($this->validated($request, $catalog, $cfg, $row->id));

        return redirect()->route('catalogs.index', $catalog)->with('success', ucfirst($cfg['singular']).' actualizada correctamente.');
    }

    public function destroy(string $catalog, int $id)
    {
        $cfg = $this->config($catalog);
        $row = $cfg['model']::findOrFail($id);

        if ($this->inUse($cfg, $row->id)) {
            return redirect()->route('catalogs.index', $catalog)
                ->with('error', 'No se puede eliminar «'.$row->name.'» porque ya está en uso (RN6).');
        }

        $row->delete();

        return redirect()->route('catalogs.index', $catalog)->with('success', ucfirst($cfg['singular']).' eliminada.');
    }

    private function config(string $catalog): array
    {
        abort_unless(isset(self::CATALOGS[$catalog]), 404);

        return self::CATALOGS[$catalog];
    }

    private function inUse(array $cfg, int $id): bool
    {
        [$table, $column] = $cfg['usage'];

        return DB::table($table)->where($column, $id)->exists();
    }

    private function validated(Request $request, string $catalog, array $cfg, ?int $ignoreId = null): array
    {
        $rules = [];
        foreach ($cfg['fields'] as $f) {
            $r = [$f['required'] ? 'required' : 'nullable'];
            match ($f['type']) {
                'money' => array_push($r, 'numeric', 'min:0', 'max:99999999.99'),
                'days' => array_push($r, 'integer', 'min:0', 'max:365'),
                'url' => array_push($r, 'url', 'max:'.$f['max']),
                default => array_push($r, 'string', 'max:'.$f['max']),
            };
            if (! empty($f['unique'])) {
                $r[] = Rule::unique($cfg['table'], $f['name'])->ignore($ignoreId);
            }
            $rules[$f['name']] = $r;
        }

        $data = $request->validate($rules, [
            'name.unique' => 'Ya existe un registro con ese nombre.',
            'website.url' => 'Escribe la dirección completa, por ejemplo https://www.marca.com.',
        ]);

        // CATEGORIA guarda además un «slug» único derivado del nombre.
        if ($catalog === 'categories') {
            $data['slug'] = Str::slug($data['name']);
            $clash = Category::where('slug', $data['slug'])->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
            if ($clash) {
                throw ValidationException::withMessages(['name' => 'Ya existe una categoría con un nombre equivalente.']);
            }
        }

        return $data;
    }
}
