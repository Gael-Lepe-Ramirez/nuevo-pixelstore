<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Entidad ORDEN_COMPRA del modelo de datos (reabastecimiento a un proveedor).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            // ON DELETE RESTRICT: no se borra un proveedor con órdenes de compra (RN6)
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->date('ordered_at');
            $table->date('received_at')->nullable();
            $table->string('status', 15)->default('pendiente'); // pendiente, recibida, cancelada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
