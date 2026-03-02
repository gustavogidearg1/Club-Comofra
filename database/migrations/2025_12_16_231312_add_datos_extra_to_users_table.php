<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {

      $table->string('cuil', 13)->nullable()->after('email')->index();
      $table->string('direccion')->nullable()->after('cuil');

      // 🔸 dejo los 3 para que tengas el dato “directo” y puedas filtrar fácil,
      // aunque Localidad -> Provincia -> País ya lo resuelve por relaciones.
      $table->foreignId('pais_id')->nullable()->after('direccion')->constrained('paises')->nullOnDelete();
      $table->foreignId('provincia_id')->nullable()->after('pais_id')->constrained('provincias')->nullOnDelete();
      $table->foreignId('localidad_id')->nullable()->after('provincia_id')->constrained('localidades')->nullOnDelete();

      $table->string('imagen')->nullable()->after('localidad_id'); // ruta en storage/public
      $table->date('fecha_nacimiento')->nullable()->after('imagen');

      $table->boolean('activo')->default(true)->after('fecha_nacimiento');
    });
  }

  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropConstrainedForeignId('localidad_id');
      $table->dropConstrainedForeignId('provincia_id');
      $table->dropConstrainedForeignId('pais_id');

      $table->dropColumn([
        'cuil','direccion','imagen','fecha_nacimiento','activo'
      ]);
    });
  }
};
