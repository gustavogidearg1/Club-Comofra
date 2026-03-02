<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_references', function (Blueprint $table) {
            // Si ya existiera, evitamos romper (en algunos entornos)
            if (!Schema::hasColumn('point_references', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()->after('id');
            }

            if (!Schema::hasColumn('point_references', 'name')) {
                $table->string('name', 120)->after('company_id');
            }

            if (!Schema::hasColumn('point_references', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('name');
            }

            if (!Schema::hasColumn('point_references', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
            }

            if (!Schema::hasColumn('point_references', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('sort_order');
            }

            if (!Schema::hasColumn('point_references', 'updated_by_user_id')) {
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('created_by_user_id');
            }

            // índice útil para listar por compañía/orden
            $table->index(['company_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('point_references', function (Blueprint $table) {
            // primero dropear FKs y luego columnas
            if (Schema::hasColumn('point_references', 'updated_by_user_id')) {
                $table->dropConstrainedForeignId('updated_by_user_id');
            }
            if (Schema::hasColumn('point_references', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
            if (Schema::hasColumn('point_references', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }

            if (Schema::hasColumn('point_references', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('point_references', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('point_references', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            // index (si existe)
            // Laravel suele dropear indices automáticamente al dropear columnas, pero por las dudas:
            // $table->dropIndex(['company_id', 'sort_order']);
        });
    }
};
