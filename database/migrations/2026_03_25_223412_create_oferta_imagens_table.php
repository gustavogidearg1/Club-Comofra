<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oferta_imagenes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oferta_id')->constrained('ofertas')->onDelete('cascade');

            $table->string('ruta');
            $table->integer('orden')->default(0);
            $table->boolean('principal')->default(false);

            $table->timestamps();

            $table->index(['oferta_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_imagenes');
    }
};
