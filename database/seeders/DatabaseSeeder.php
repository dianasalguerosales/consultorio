<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Catálogos base
            CargoSeeder::class,
            EstadoCivilSeeder::class,
            GeneroSeeder::class,
            ServicioSeeder::class,
            ProgramaSeeder::class,
            EstadoCitaSeeder::class,
            TipoCitaSeeder::class,
            DiagnosticoSeeder::class,
            EscolaridadSeeder::class,
            RelacionesPacienteSeeder::class,
            EstadoExpedienteSeeder::class,

            // 2. Usuarios y roles
            UsersSeeder::class,
            RolesSeeder::class,
            UserRolesSeeder::class,
            RolesAndPermissionsSeeder::class,

            // 3. Entidades principales
            AdministrativosSeeder::class,
            TerapeutasSeeder::class,
            EncargadosSeeder::class,
            PacientesSeeder::class,
            ExpedienteSeeder::class,
            EncargadoPacienteSeeder::class,

            // 4. Datos dinámicos
            CitaSeeder::class,
            PagoSeeder::class,
            SesionSeeder::class,
            EvaluacionesSeeder::class,
            Criterios1Seeder::class,
            Criterios2Seeder::class,
            Criterios3Seeder::class,
        ]);
    }
}