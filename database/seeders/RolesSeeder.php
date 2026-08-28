<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['administrador', 'coordinador', 'terapeuta', 'encargado', 'pruebas'];

        foreach ($roles as $rol) {
            Role::updateOrCreate(
                ['name' => $rol, 'guard_name' => 'web'],
                ['name' => $rol, 'guard_name' => 'web']
            );
        }
    }
}
