<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Entidad PROVEEDOR del modelo de datos.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);              // razón social
            $table->string('rfc', 13)->unique();
            $table->string('email', 100);
            $table->string('phone', 15);
            $table->string('address', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
