<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Llave de Tenancy
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock_count')->default(0)->comment('Inventario total');

            // Módulo QuickOrder
            $table->boolean('is_visible_on_quickorder')->default(false);

            // Módulo Synchro (Sensores)
            $table->integer('unit_weight_grams')->nullable()->comment('Peso de 1 unidad');
            $table->integer('low_stock_threshold')->nullable()->default(3)->comment('Alertar cuando queden X unidades');

            $table->timestamps();

            // Un SKU debe ser único, pero solo DENTRO de la misma tienda
            $table->unique(['store_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
