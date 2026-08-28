<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRolesSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'admin@caine.com')->first()?->assignRole('administrador');
        User::where('email', 'juan@example.com')->first()?->assignRole('terapeuta');
        User::where('email', 'maria@example.com')->first()?->assignRole('coordinador');
        User::where('email', 'carlos@example.com')->first()?->assignRole('encargado');
        User::where('email', 'test@caine.com')->first()?->assignRole('pruebas');
    }
}