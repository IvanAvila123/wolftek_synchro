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
        Schema::create('smart_shelves', function (Blueprint $table) {
            $table->id();

            // Llave de Tenancy
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // Qué producto está vigilando este sensor
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('hardware_id')->unique()->comment('ID único del chip ESP32');
            $table->string('status')->default('ok')->comment('ok, low, empty');
            $table->integer('current_weight')->nullable();
            $table->timestamp('last_reported_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_shelves');
    }
};
