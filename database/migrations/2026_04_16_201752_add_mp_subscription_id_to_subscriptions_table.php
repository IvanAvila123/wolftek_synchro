<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('mp_subscription_id')->nullable()->after('conekta_subscription_id');
            $table->string('mp_payment_id')->nullable()->after('mp_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['mp_subscription_id', 'mp_payment_id']);
        });
    }
};
