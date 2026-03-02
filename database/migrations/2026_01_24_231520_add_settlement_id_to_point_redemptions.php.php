<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('point_redemptions', function (Blueprint $table) {
      $table->unsignedBigInteger('settlement_id')->nullable()->after('point_movement_id')->index();

      $table->foreign('settlement_id')
        ->references('id')->on('point_settlements')
        ->onDelete('set null');
    });
  }

  public function down(): void
  {
    Schema::table('point_redemptions', function (Blueprint $table) {
      $table->dropForeign(['settlement_id']);
      $table->dropColumn('settlement_id');
    });
  }
};
