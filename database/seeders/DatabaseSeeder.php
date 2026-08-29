<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            RolesSeeder::class,
            UserRolesSeeder::class,
            TerapeutasSeeder::class,
            EncargadosSeeder::class,
            PacientesSeeder::class,
            RolesAndPermissionsSeeder::class,
            ServicioSeeder::class,
            ProgramaSeeder::class,
            EstadoCitaSeeder::class,
            CitaSeeder::class,
        ]);
    }
}
