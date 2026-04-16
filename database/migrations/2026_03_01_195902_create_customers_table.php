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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // relación con tiendas
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(0); // límite de crédito para compras a crédito
            $table->decimal('balance', 10, 2)->default(0); // balance actual de lo que se le debe al cliente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
