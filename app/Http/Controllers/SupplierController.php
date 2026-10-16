<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Administración de PROVEEDOR (solo admin; ver routes/web.php).
class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier)
    {
        // ON DELETE RESTRICT (RN6): un proveedor con órdenes de compra no se puede borrar.
        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'No se puede eliminar a «'.$supplier->name.'» porque tiene órdenes de compra registradas.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        // El RFC se guarda en mayúsculas y sin espacios.
        $request->merge(['rfc' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('rfc')))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rfc' => ['required', 'string', 'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                Rule::unique('suppliers', 'rfc')->ignore($supplier?->id)],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string', 'max:150'],
        ], [
            'rfc.regex' => 'El RFC debe tener 12 o 13 caracteres (por ejemplo, ABC010203XY1).',
            'rfc.unique' => 'Ya existe un proveedor con ese RFC.',
        ]);
    }
}
