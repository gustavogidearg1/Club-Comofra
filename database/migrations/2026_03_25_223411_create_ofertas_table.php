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
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('titulo');
            $table->string('slug')->nullable()->unique();

            $table->string('descripcion_corta', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();

            $table->decimal('precio', 12, 2)->nullable();
            $table->decimal('precio_anterior', 12, 2)->nullable();

            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();

            $table->boolean('publicada')->default(false);
            $table->boolean('destacada')->default(false);

            $table->boolean('enviar_correo')->default(false);
            $table->boolean('correo_enviado')->default(false);
            $table->timestamp('fecha_envio_correo')->nullable();

            $table->string('estado')->default('borrador'); // borrador, publicada, pausada, vencida
            $table->integer('orden')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'publicada']);
            $table->index(['fecha_desde', 'fecha_hasta']);
            $table->index(['destacada', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
