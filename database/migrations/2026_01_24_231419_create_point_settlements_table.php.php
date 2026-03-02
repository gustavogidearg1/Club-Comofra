<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('point_settlements', function (Blueprint $table) {
      $table->id();

      $table->unsignedBigInteger('company_id')->index();
      $table->unsignedBigInteger('business_user_id')->index();

      $table->date('period_from')->nullable()->index();
      $table->date('period_to')->nullable()->index();

      $table->integer('total_points')->default(0);
      $table->decimal('total_amount', 12, 2)->nullable(); // si querés $ (1 punto = 1)

      $table->string('status', 20)->default('draft')->index(); // draft|invoiced|cancelled
      $table->timestamp('invoiced_at')->nullable();
      $table->unsignedBigInteger('invoiced_by')->nullable();

      $table->string('invoice_number', 60)->nullable();
      $table->string('note', 500)->nullable();

      $table->timestamps();

      $table->foreign('business_user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('invoiced_by')->references('id')->on('users')->onDelete('set null');
      // company_id -> companies (si tenés tabla companies con FK, agregalo también)
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('point_settlements');
  }
};
