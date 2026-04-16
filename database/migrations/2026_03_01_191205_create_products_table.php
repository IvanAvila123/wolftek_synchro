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
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // relación con tiendas
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // relación con categorías
            $table->string('name');
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_buy', 10, 2)->nullable(0); // precio de compra
            $table->decimal('price_sell', 10, 2); // precio de venta
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(5); // para alertas de stock bajo
            $table->enum('unidad', ['pieza', 'kg', 'litro','gramo'])->default('pieza'); // unidad de medida
            $table->boolean('has_scale')->default(false); // para productos que se venden por peso
            $table->boolean('is_active')->default(true); // para desactivar productos sin eliminarlos
            $table->timestamps();
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
