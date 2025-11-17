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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Usamos nullOnDelete() para que si un producto se borra,
            // el historial de la orden no se pierda.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('quantity');
            $table->decimal('price_at_purchase', 10, 2)->comment('El precio al que se vendió');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
