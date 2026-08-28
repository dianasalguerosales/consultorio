<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de pruebas
        User::updateOrCreate(
            ['email' => 'test@caine.com'],
            [
                'name' => 'Usuario Prueba',
                'password' => Hash::make('123456'),
            ]
        );

        // Administrador
        User::updateOrCreate(
            ['email' => 'admin@caine.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'telefono' => '555-0001',
                'direccion' => 'Zona 10, Guatemala',
                'fecha_nacimiento' => '1980-01-01',
                'genero' => 'Masculino',
                'nacionalidad' => 'Guatemalteco',
                'estado_civil' => 'Casado',
                'idioma' => 'es',
                'estado' => 'activo',
            ]
        );

        // Terapeuta
        User::updateOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('terapeuta123'),
            ]
        );

        // Coordinador
        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name' => 'María López',
                'password' => Hash::make('coordinador123'),
            ]
        );

        // Encargado
        User::updateOrCreate(
            ['email' => 'carlos@example.com'],
            [
                'name' => 'Carlos Gómez',
                'password' => Hash::make('encargado123'),
            ]
        );

        // Otro encargado
        User::updateOrCreate(
            ['email' => 'ana@example.com'],
            [
                'name' => 'Ana Torres',
                'password' => Hash::make('encargado456'),
            ]
        );
    }
}
