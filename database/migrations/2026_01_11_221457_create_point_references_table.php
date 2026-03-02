<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('point_references', function (Blueprint $table) {
      $table->id();

      // Si querés referencias por empresa (recomendado)
      $table->foreignId('company_id')->nullable()
        ->constrained('companies')->nullOnDelete();

      // Relación con usuario creador (ABM)
      $table->foreignId('created_by_user_id')->nullable()
        ->constrained('users')->nullOnDelete();

      // (opcional) quien la editó por última vez
      $table->foreignId('updated_by_user_id')->nullable()
        ->constrained('users')->nullOnDelete();

      $table->string('name', 120);
      $table->boolean('is_active')->default(true);
      $table->unsignedSmallInteger('sort_order')->default(0);

      $table->timestamps();

      $table->unique(['company_id', 'name']); // evita duplicados por empresa
      $table->index(['company_id', 'is_active']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('point_references');
  }
};
