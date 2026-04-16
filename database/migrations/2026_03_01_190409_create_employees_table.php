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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // relación con usuarios
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // relación con tiendas
            $table->string('rfc')->nullable();
            $table->string('curp')->nullable();
            $table->string('fiscal_document')->nullable(); // para CFDI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
