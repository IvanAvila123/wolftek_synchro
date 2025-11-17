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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Llave de Tenancy
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('status')->default('pendiente')->comment('pendiente, en_preparacion, listo_para_recoger, completado');
            $table->decimal('total', 10, 2);
            $table->string('payment_method')->nullable()->comment('efectivo, transferencia, mercadopago');
            $table->string('payment_status')->default('pendiente')->comment('pendiente, pagado');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
