<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['administrador','terapeuta','coordinador','encargado','pruebas'];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
        }

        $usuariosRoles = [
            'admin@caine.com' => 'administrador',
            'juan@example.com' => 'terapeuta',
            'maria@example.com' => 'coordinador',
            'carlos@example.com' => 'encargado',
            'test@caine.com' => 'pruebas',
        ];

        foreach ($usuariosRoles as $email => $rol) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->assignRole($rol);
            }
        }
    }
}