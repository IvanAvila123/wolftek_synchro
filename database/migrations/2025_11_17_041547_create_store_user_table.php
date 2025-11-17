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
        Schema::create('store_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Conexión a la tabla 'stores' que acabamos de crear
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // Definimos una llave primaria compuesta
            $table->primary(['user_id', 'store_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_user');
    }
};
