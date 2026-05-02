<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('months')->default(1);
            $table->string('payment_method');         // 'mercadopago', 'transfer'
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('reference')->nullable();  // Folio/referencia SPEI
            $table->text('notes')->nullable();        // Notas del dueño de tienda
            $table->text('admin_notes')->nullable();  // Notas del admin al aprobar/rechazar
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
