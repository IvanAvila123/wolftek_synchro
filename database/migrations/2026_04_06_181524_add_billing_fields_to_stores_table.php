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
        Schema::table('stores', function (Blueprint $table) {
            
            // Fecha en la que se vence su mensualidad
            $table->date('valid_until')->nullable();
            
            // El interruptor maestro de apagado/encendido
            $table->boolean('is_active')->default(true); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([ 'valid_until', 'is_active']);
        });
    }
};
