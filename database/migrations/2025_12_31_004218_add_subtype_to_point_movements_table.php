<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->string('subtype', 80)->nullable()->after('type');
            $table->index(['type', 'subtype']);
        });
    }

    public function down(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->dropIndex(['type', 'subtype']);
            $table->dropColumn('subtype');
        });
    }
};

