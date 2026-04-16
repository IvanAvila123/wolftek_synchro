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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // dueño de la tienda
            $table->foreignId('plan_id')->constrained()->onDelete('cascade'); // plan de suscripción
            $table->string('name');
            $table->string('rfc')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('catalog_description')->nullable();
            $table->string('stripe_customer_id')->nullable(); // para suscripciones
            $table->enum('estatus', ['activo', 'suspendido', 'cancelado'])->default('activo'); // para suspender acceso si no paga\
            $table->timestamp('trial_ends_at')->nullable(); // para periodo de prueba
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
