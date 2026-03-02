<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('provincias', function (Blueprint $table) {
      $table->id();
      $table->foreignId('pais_id')->constrained('paises')->cascadeOnDelete();
      $table->string('nombre');
      $table->timestamps();

      $table->unique(['pais_id','nombre']);
      $table->index(['pais_id','nombre']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('provincias');
  }
};

