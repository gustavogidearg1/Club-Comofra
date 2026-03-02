<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;

class SouthAmericaCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['nombre' => 'Argentina',   'iso2' => 'AR', 'iso3' => 'ARG'],
            ['nombre' => 'Bolivia',     'iso2' => 'BO', 'iso3' => 'BOL'],
            ['nombre' => 'Brasil',      'iso2' => 'BR', 'iso3' => 'BRA'],
            ['nombre' => 'Chile',       'iso2' => 'CL', 'iso3' => 'CHL'],
            ['nombre' => 'Colombia',    'iso2' => 'CO', 'iso3' => 'COL'],
            ['nombre' => 'Ecuador',     'iso2' => 'EC', 'iso3' => 'ECU'],
            ['nombre' => 'Guyana',      'iso2' => 'GY', 'iso3' => 'GUY'],
            ['nombre' => 'Paraguay',    'iso2' => 'PY', 'iso3' => 'PRY'],
            ['nombre' => 'Perú',        'iso2' => 'PE', 'iso3' => 'PER'],
            ['nombre' => 'Surinam',     'iso2' => 'SR', 'iso3' => 'SUR'],
            ['nombre' => 'Uruguay',     'iso2' => 'UY', 'iso3' => 'URY'],
            ['nombre' => 'Venezuela',   'iso2' => 'VE', 'iso3' => 'VEN'],
        ];

        foreach ($countries as $c) {
            Pais::updateOrCreate(
                ['nombre' => $c['nombre']],
                [
                    'iso2' => $c['iso2'],
                    'iso3' => $c['iso3'],
                ]
            );
        }
    }
}
