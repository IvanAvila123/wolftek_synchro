<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeStoreIdNullableInPermissionTables extends Migration
{
    public function up(): void
    {
        // 1. model_has_roles: quitar FK, quitar PK, hacer nullable, crear unique index
        Schema::table('model_has_roles', function (Blueprint $table) {
            // Quitar foreign key primero
            $table->dropForeign(['role_id']);
        });

        // Quitar primary key (requiere statement directo)
        DB::statement('ALTER TABLE model_has_roles DROP PRIMARY KEY');

        Schema::table('model_has_roles', function (Blueprint $table) {
            // Hacer store_id nullable
            $table->unsignedBigInteger('store_id')->nullable()->change();
            // Unique index que incluye store_id
            $table->unique(['role_id', 'model_id', 'model_type', 'store_id'], 'model_has_roles_unique');
            // Recrear foreign key
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        // 2. model_has_permissions: mismo proceso
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
        });

        DB::statement('ALTER TABLE model_has_permissions DROP PRIMARY KEY');

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->change();
            $table->unique(['permission_id', 'model_id', 'model_type', 'store_id'], 'model_has_permissions_unique');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });

        // 3. roles: solo hacer nullable
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revertir cambios si es necesario
    }
}
