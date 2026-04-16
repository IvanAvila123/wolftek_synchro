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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_shift_id')->constrained()->cascadeOnDelete(); // Para saber de qué turno salió el dinero
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Quién sacó el dinero
            
            $table->string('concept'); // Ej: "Pago proveedor Coca-Cola", "Compra de trapeador"
            $table->decimal('amount', 10, 2); // Cuánto dinero sacó
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
