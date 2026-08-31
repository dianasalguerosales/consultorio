<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Escolaridad;

class EscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        Escolaridad::updateOrCreate(['grado' => 'Prekinder']);
        Escolaridad::updateOrCreate(['grado' => 'Kinder']);
        Escolaridad::updateOrCreate(['grado' => 'Preparatoria']);
        Escolaridad::updateOrCreate(['grado' => '1ro Primaria']);
        Escolaridad::updateOrCreate(['grado' => '2do Primaria']);
        Escolaridad::updateOrCreate(['grado' => '3ro Primaria']);
        Escolaridad::updateOrCreate(['grado' => '4to Primaria']);
        Escolaridad::updateOrCreate(['grado' => '5to Primaria']);
        Escolaridad::updateOrCreate(['grado' => '6to Primaria']);
        Escolaridad::updateOrCreate(['grado' => '1ro Básico']);
        Escolaridad::updateOrCreate(['grado' => '2do Básico']);
        Escolaridad::updateOrCreate(['grado' => '3ro Básico']);
        Escolaridad::updateOrCreate(['grado' => 'No aplica']);
    }
}