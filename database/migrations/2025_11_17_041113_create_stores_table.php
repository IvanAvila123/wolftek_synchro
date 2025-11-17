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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->string('whatsapp_number')->comment('Número para recibir pedidos');
            $table->string('slug')->unique()->comment('Para la URL de QuickOrder');

            //opciones de pago
            $table->string('mercado_pago_key')->nullable();
            $table->string('clabe_interbancaria', 18)->nullable()->comment('Para pagos por transferencia bancaria');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
