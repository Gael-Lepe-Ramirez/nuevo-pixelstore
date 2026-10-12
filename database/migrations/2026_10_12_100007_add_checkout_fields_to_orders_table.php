<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Relaciones «usa» (método de envío) y «paga» (método de pago), estado de pago
// y dirección de entrega histórica (RN7, RN8).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->nullable()->after('shipping_method_id')->constrained()->restrictOnDelete();
            $table->string('payment_status', 15)->default('pendiente')->after('status'); // pendiente, pagado, rechazado
            $table->string('shipping_address', 255)->nullable()->after('payment_status');
        });

        // Pedidos anteriores al cambio: se completan con valores genéricos para poder exigir NOT NULL.
        if (DB::table('orders')->exists()) {
            $shipping = DB::table('shipping_methods')->insertGetId([
                'name' => 'Sin especificar', 'cost' => 0, 'estimated_days' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $payment = DB::table('payment_methods')->insertGetId([
                'name' => 'Sin especificar', 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('orders')->update([
                'shipping_method_id' => $shipping,
                'payment_method_id' => $payment,
                'shipping_address' => 'No registrada',
            ]);
        }

        // Estados del modelo: pendiente, enviado, entregado, cancelado.
        DB::table('orders')->whereIn('status', ['pending', 'Completada'])->update(['status' => 'pendiente']);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable(false)->change();
            $table->foreignId('payment_method_id')->nullable(false)->change();
            $table->string('shipping_address', 255)->nullable(false)->change();
            $table->string('status', 15)->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropConstrainedForeignId('payment_method_id');
            $table->dropColumn(['payment_status', 'shipping_address']);
            $table->string('status')->default('pending')->change();
        });
    }
};
