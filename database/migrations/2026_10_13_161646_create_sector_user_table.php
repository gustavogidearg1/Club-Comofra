<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sector_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sector_id')
                ->constrained('sectors')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->boolean('principal')->default(false);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['sector_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sector_user');
    }
};
