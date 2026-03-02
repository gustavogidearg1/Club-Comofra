<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono', 30)->nullable()->after('direccion');

            // Si querés que sea único por empresa + teléfono (opcional):
            // $table->unique(['company_id', 'telefono'], 'users_company_telefono_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Si activaste el unique, descomentá:
            // $table->dropUnique('users_company_telefono_unique');

            $table->dropColumn('telefono');
        });
    }
};
