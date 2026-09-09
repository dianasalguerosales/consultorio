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

            // Agenda. 'gestionar citas' no alcanzaba para separar quién puede
            // ver el calendario de quién puede crear citas en él.
            'ver agenda',
            'agendar citas',
            'ver ocupacion personal',

            // Indicadores: vistas analíticas de toda la población de pacientes.
            'ver indicadores',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Definir roles
        $administrador = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $coordinador   = Role::firstOrCreate(['name' => 'coordinador', 'guard_name' => 'web']);
        $terapeuta     = Role::firstOrCreate(['name' => 'terapeuta', 'guard_name' => 'web']);
        $auxiliar      = Role::firstOrCreate(['name' => 'auxiliar', 'guard_name' => 'web']);
        $encargado     = Role::firstOrCreate(['name' => 'encargado', 'guard_name' => 'web']);
        $pruebas       = Role::firstOrCreate(['name' => 'pruebas', 'guard_name' => 'web']);

        // syncPermissions en lugar de givePermissionTo: deja el rol exactamente
        // con lo que se lista aquí, así quitar un permiso también surte efecto
        // al re-sembrar.
        $administrador->syncPermissions(Permission::all());

        $coordinador->syncPermissions([
            'gestionar citas',
            'ver reportes',
            'gestionar pacientes',
            'ver agenda',
            'agendar citas',
            'ver ocupacion personal',
            'ver indicadores',
        ]);

        // El terapeuta solo consulta la agenda: ve sus propias citas pero no
        // las crea, por eso ya no lleva 'gestionar citas'.
        $terapeuta->syncPermissions([
            'gestionar pacientes',
            'crear informes',
            'editar informes',
            'ver agenda',
        ]);

        // El auxiliar atiende solo en sucursal, así que agenda sus propias citas.
        $auxiliar->syncPermissions([
            'gestionar pacientes',
            'gestionar citas',
            'ver agenda',
            'agendar citas',
        ]);

        // El encargado solo ve la agenda de sus hijos. Sin 'ver reportes': los
        // informes cruzan a todos los pacientes del consultorio.
        $encargado->syncPermissions([
            'acceso portal padres',
            'ver agenda',
        ]);

        // Rol pruebas: sin permisos o mínimos
        $pruebas->syncPermissions([
            'ver reportes',
        ]);
    }
}
