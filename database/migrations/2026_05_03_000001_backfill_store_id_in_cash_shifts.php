<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rellena store_id usando la caja registradora asociada al turno
        DB::statement('
            UPDATE cash_shifts cs
            JOIN cash_registers cr ON cr.id = cs.cash_register_id
            SET cs.store_id = cr.store_id
            WHERE cs.store_id IS NULL
        ');
    }

    public function down(): void
    {
        //
    }
};
