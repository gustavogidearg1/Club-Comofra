<?php

// database/migrations/2025_12_22_000001_create_point_import_batches_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('point_import_batches', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index(); // user_id

            $table->string('filename');
            $table->string('status')->default('preview'); // preview|imported|failed

            $table->unsignedInteger('rows_total')->default(0);
            $table->unsignedInteger('rows_ok')->default(0);
            $table->unsignedInteger('rows_error')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('point_import_batches');
    }
};
