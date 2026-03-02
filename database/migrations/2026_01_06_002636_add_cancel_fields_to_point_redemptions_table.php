<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->string('cancel_note', 500)->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('point_redemptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at','cancel_note']);
        });
    }
};
