<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// DETALLE_PEDIDO: un producto solo aparece una vez por pedido y se elimina la columna
// «price», que duplicaba a «unit_price» (precio vigente al momento de la compra, RN3).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'product_id']);
            $table->decimal('price', 10, 2)->default(0);
        });
    }
};
