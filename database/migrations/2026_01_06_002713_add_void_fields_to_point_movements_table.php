<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('occurred_at');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->string('void_note', 500)->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('point_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at','void_note']);
        });
    }
};
