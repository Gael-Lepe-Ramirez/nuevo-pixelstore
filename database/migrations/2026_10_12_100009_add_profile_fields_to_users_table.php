<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Datos del CLIENTE que el modelo pide (apellidos, teléfono, dirección). Son opcionales en el
// registro (también se entra con GitHub) y se exigen al momento de comprar.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name', 80)->nullable()->after('name');
            $table->string('phone', 15)->nullable()->after('email');
            $table->string('shipping_address', 255)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'phone', 'shipping_address']);
        });
    }
};
