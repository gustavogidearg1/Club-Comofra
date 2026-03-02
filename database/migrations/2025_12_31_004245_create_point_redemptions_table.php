<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('employee_user_id');
            $table->unsignedBigInteger('business_user_id');
            $table->unsignedBigInteger('created_by'); // quien generó la solicitud (negocio)
            $table->unsignedBigInteger('point_movement_id')->nullable();

            $table->integer('points'); // positivo
            $table->string('reference', 120)->nullable(); // default "GASTO_NEGOCIO"
            $table->text('note')->nullable();

            $table->string('status', 20)->default('pending'); // pending|confirmed|cancelled|expired
            $table->string('token', 64)->unique();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'employee_user_id']);
            $table->index(['business_user_id', 'status']);
            $table->index(['expires_at']);

            // FK (si querés estrictas, agregalas; si preferís liviano, dejalo así)
            // $table->foreign('company_id')->references('id')->on('companies');
            // $table->foreign('employee_user_id')->references('id')->on('users');
            // $table->foreign('business_user_id')->references('id')->on('users');
            // $table->foreign('created_by')->references('id')->on('users');
            // $table->foreign('point_movement_id')->references('id')->on('point_movements');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_redemptions');
    }
};
