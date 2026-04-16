<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // 'admin' = panel de administración, 'cashier' = panel de caja, null = sin acceso a ningún panel
            $table->string('panel')->nullable()->default(null)->after('guard_name');
        });

        // Asignar panel a los roles del sistema que ya existen
        DB::table('roles')->where('name', 'owner')->update(['panel' => 'admin']);
        DB::table('roles')->where('name', 'manager')->update(['panel' => 'admin']);
        DB::table('roles')->where('name', 'cashier')->update(['panel' => 'cashier']);
        // super_admin no usa este campo — tiene su propio panel
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('panel');
        });
    }
};
