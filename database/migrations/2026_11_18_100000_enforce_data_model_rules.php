<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes para que la base cumpla el modelo de datos (revisión de la actividad 4.1):
 *  - CATEGORIA.descripcion, que el modelo define y la tabla no tenía.
 *  - ON DELETE RESTRICT (RN6) en las llaves que venían en cascada desde la versión original:
 *    un cliente con pedidos, una categoría con productos y un producto vendido ya no se pueden borrar.
 *  - Restricciones CHECK de precios, cantidades, estados y fechas (en MySQL/MariaDB).
 */
return new class extends Migration
{
    /** nombre => [tabla, condición] */
    private const CHECKS = [
        'products_price_positive' => ['products', 'price > 0'],
        'orders_total_nonnegative' => ['orders', 'total_amount >= 0'],
        'orders_status_valid' => ['orders', "status IN ('pendiente', 'enviado', 'entregado', 'cancelado')"],
        'orders_payment_status_valid' => ['orders', "payment_status IN ('pendiente', 'pagado', 'rechazado')"],
        'order_product_quantity_positive' => ['order_product', 'quantity >= 1'],
        'order_product_unit_price_nonnegative' => ['order_product', 'unit_price >= 0'],
        'purchase_orders_status_valid' => ['purchase_orders', "status IN ('pendiente', 'recibida', 'cancelada')"],
        'purchase_orders_dates_valid' => ['purchase_orders', 'received_at IS NULL OR received_at >= ordered_at'],
        'purchase_order_product_quantity_positive' => ['purchase_order_product', 'quantity >= 1'],
        'purchase_order_product_unit_cost_nonnegative' => ['purchase_order_product', 'unit_cost >= 0'],
        'shipping_methods_cost_nonnegative' => ['shipping_methods', 'cost >= 0'],
    ];

    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('description', 150)->nullable()->after('slug');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        if ($this->supportsChecks()) {
            foreach (self::CHECKS as $name => [$table, $condition]) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$condition})");
            }
        }
    }

    public function down(): void
    {
        if ($this->supportsChecks()) {
            foreach (self::CHECKS as $name => [$table]) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$name}");
            }
        }

        Schema::table('order_product', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    private function supportsChecks(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb']);
    }
};
