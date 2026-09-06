<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'gestionar usuarios',
            'gestionar pacientes',
            'gestionar citas',
            'ver reportes',
            'crear informes',
            'editar informes',
            'eliminar informes',
            'acceso portal padres',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Definir roles
        $administrador = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $coordinador   = Role::firstOrCreate(['name' => 'coordinador', 'guard_name' => 'web']);
        $terapeuta     = Role::firstOrCreate(['name' => 'terapeuta', 'guard_name' => 'web']);
        $encargado     = Role::firstOrCreate(['name' => 'encargado', 'guard_name' => 'web']);
        $pruebas       = Role::firstOrCreate(['name' => 'pruebas', 'guard_name' => 'web']);

        // Asignar permisos a cada rol
        $administrador->givePermissionTo(Permission::all()); // todos los permisos

        $terapeuta->givePermissionTo([
            'gestionar pacientes',
            'gestionar citas',
            'crear informes',
            'editar informes',
        ]);

        $coordinador->givePermissionTo([
            'gestionar citas',
            'gestionar pacientes',
        ]);

        $encargado->givePermissionTo([
            'acceso portal padres',
            'ver reportes',
        ]);

        // Rol pruebas: sin permisos o mínimos
        $pruebas->givePermissionTo([
            'ver reportes',
        ]);
    }
}