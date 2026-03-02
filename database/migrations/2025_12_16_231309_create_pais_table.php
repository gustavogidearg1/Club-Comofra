<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('paises', function (Blueprint $table) {
      $table->id();
      $table->string('nombre')->unique();
      $table->string('iso2', 2)->nullable()->index();   // AR, BR, etc.
      $table->string('iso3', 3)->nullable()->index();   // ARG, BRA, etc.
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('paises');
  }
};
