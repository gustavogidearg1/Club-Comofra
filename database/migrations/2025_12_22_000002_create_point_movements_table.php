<?php

// database/migrations/2025_12_22_000002_create_point_movements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('point_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('employee_user_id')->index(); // Empleado (client)
            $table->unsignedBigInteger('business_user_id')->nullable()->index(); // Negocio (staff) opcional
            $table->unsignedBigInteger('created_by')->index(); // quién lo cargó

            $table->unsignedBigInteger('batch_id')->nullable()->index();

            // earn = suma, redeem = resta, adjust = ajuste
            $table->string('type')->default('earn');

            // positivo o negativo (permitimos negativos por flexibilidad)
            $table->integer('points');

            $table->decimal('money_amount', 12, 2)->nullable(); // monto gastado, opcional
            $table->string('reference')->nullable();           // ticket / campaña / etc
            $table->text('note')->nullable();

            $table->dateTime('occurred_at')->index();

            $table->timestamps();

            // Si tenés FK a users:
            // $table->foreign('employee_user_id')->references('id')->on('users');
            // $table->foreign('business_user_id')->references('id')->on('users');
            // $table->foreign('created_by')->references('id')->on('users');
            // $table->foreign('batch_id')->references('id')->on('point_import_batches');
        });
    }

    public function down(): void {
        Schema::dropIfExists('point_movements');
    }
};
