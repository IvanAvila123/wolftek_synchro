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
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            
            // Datos del cliente
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('notes')->nullable();
            
            // Total a pagar
            $table->decimal('total', 10, 2);
            
            // Estado del pedido (pending, accepted, completed, cancelled)
            $table->string('status')->default('pending');
            
            // Guardamos el JSON con todo lo que pidió (para no crear otra tabla de detalles por ahora y hacerlo súper rápido)
            $table->json('cart_items');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};
