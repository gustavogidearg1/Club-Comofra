<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;
use App\Models\Provincia;

class ArgentinaProvincesSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar Argentina por ISO2 y fallback por nombre
        $arg = Pais::query()
            ->where('iso2', 'AR')
            ->orWhere('nombre', 'Argentina')
            ->first();

        if (!$arg) {
            $this->command?->error('No se encontró el país Argentina (iso2=AR). Ejecutá primero el seeder de países.');
            return;
        }

        $provinces = [
            'Buenos Aires',
            'Catamarca',
            'Chaco',
            'Chubut',
            'Córdoba',
            'Corrientes',
            'Entre Ríos',
            'Formosa',
            'Jujuy',
            'La Pampa',
            'La Rioja',
            'Mendoza',
            'Misiones',
            'Neuquén',
            'Río Negro',
            'Salta',
            'San Juan',
            'San Luis',
            'Santa Cruz',
            'Santa Fe',
            'Santiago del Estero',
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
            'Tucumán',
            'Ciudad Autónoma de Buenos Aires',
        ];

        foreach ($provinces as $name) {
            Provincia::updateOrCreate(
                ['pais_id' => $arg->id, 'nombre' => $name],
                ['pais_id' => $arg->id, 'nombre' => $name]
            );
        }

        $this->command?->info('✅ Provincias de Argentina cargadas/actualizadas correctamente.');
    }
}
