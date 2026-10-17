<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Relación «fabrica»: cada producto tiene exactamente una marca (FK brand_id NOT NULL).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->restrictOnDelete();
        });

        // Si ya había productos, se les asigna una marca genérica antes de volver obligatoria la columna.
        if (DB::table('products')->exists()) {
            $brandId = DB::table('brands')->insertGetId([
                'name' => 'Sin marca', 'country' => 'N/D', 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('products')->whereNull('brand_id')->update(['brand_id' => $brandId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable(false)->change();
        });

        // CHECK (cantidad_disponible >= 0): el inventario nunca queda negativo (RN2).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_stock_nonnegative CHECK (stock >= 0)');
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE products DROP CONSTRAINT products_stock_nonnegative');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
