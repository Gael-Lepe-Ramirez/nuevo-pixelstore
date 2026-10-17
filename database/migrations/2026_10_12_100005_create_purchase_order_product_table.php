<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabla intermedia DETALLE_ORDEN_COMPRA: llave primaria compuesta (orden, producto).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_product', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete(); // los renglones siguen a su orden
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');                                       // cantidad_ordenada (>= 1)
            $table->decimal('unit_cost', 10, 2);                                       // costo_unitario vigente en la orden
            $table->timestamps();

            $table->primary(['purchase_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_product');
    }
};
