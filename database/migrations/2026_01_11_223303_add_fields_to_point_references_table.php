<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_references', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->string('name', 120)->after('company_id');
            $table->boolean('is_active')->default(true)->after('name');
            $table->unsignedSmallInteger('sort_order')->nullable()->after('is_active');

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'name']); // evita duplicados por empresa
        });
    }

    public function down(): void
    {
        Schema::table('point_references', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'name']);
            $table->dropIndex(['company_id', 'is_active']);

            $table->dropColumn(['company_id','name','is_active','sort_order']);
        });
    }
};
