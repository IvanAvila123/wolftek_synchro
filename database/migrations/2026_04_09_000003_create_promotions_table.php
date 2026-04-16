<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_batch_id')->constrained()->cascadeOnDelete();
            // Desnormalizado para búsqueda rápida en POS sin join extra
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('nombre');                         // Ej: "Aspirina 3x2", "10% OFF caducidad"
            $table->enum('tipo', ['porcentaje', 'precio_fijo', 'nxm']);
            $table->decimal('valor', 10, 2)->nullable();      // % o precio fijo (null para nxm)
            $table->unsignedTinyInteger('cantidad_paga')->nullable(); // nxm: cuánto pagas
            $table->unsignedTinyInteger('cantidad_lleva')->nullable();// nxm: cuánto llevas

            $table->boolean('activa')->default(false);
            // Si > 0: se activa automáticamente cuando al lote le quedan <= X días
            $table->unsignedSmallInteger('auto_activar_dias')->nullable();

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();              // null = hasta que caduque el lote
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
