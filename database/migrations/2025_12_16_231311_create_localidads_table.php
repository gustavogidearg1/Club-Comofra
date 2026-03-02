<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('localidades', function (Blueprint $table) {
      $table->id();
      $table->foreignId('provincia_id')->constrained('provincias')->cascadeOnDelete();
      $table->string('nombre');
      $table->string('cp', 10)->nullable();
      $table->timestamps();

      $table->unique(['provincia_id','nombre']);
      $table->index(['provincia_id','nombre']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('localidades');
  }
};
