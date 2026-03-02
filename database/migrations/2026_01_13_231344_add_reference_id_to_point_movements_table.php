<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('money_amount');

            $table->index(['company_id', 'reference_id'], 'pm_company_reference_idx');

            $table->foreign('reference_id')
                ->references('id')->on('point_references')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
            $table->dropIndex('pm_company_reference_idx');
            $table->dropColumn('reference_id');
        });
    }
};
