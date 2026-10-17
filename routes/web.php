<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Rutas públicas (Cualquier persona puede ver el catálogo)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// Rutas para Autenticación con GitHub
Route::get('/auth/github/redirect', [App\Http\Controllers\Auth\OAuthController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [App\Http\Controllers\Auth\OAuthController::class, 'callback']);

// Rutas protegidas (Solo el 'admin' puede administrar inventario)
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Abastecimiento: proveedores y órdenes de compra (el stock entra al recibir una orden)
    Route::prefix('admin')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/carrito', [OrderController::class, 'cart'])->name('cart.index');
    Route::post('/carrito/agregar/{product}', [OrderController::class, 'add'])->name('cart.add');
    Route::post('/carrito/remover/{id}', [OrderController::class, 'remove'])->name('cart.remove');
    Route::post('/comprar', [OrderController::class, 'checkout'])->name('cart.checkout');
    Route::get('/orders/{order}/pdf', [App\Http\Controllers\OrderController::class, 'downloadPDF'])->name('orders.pdf');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');
});