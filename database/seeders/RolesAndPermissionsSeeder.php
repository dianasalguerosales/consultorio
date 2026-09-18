<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Los permisos del sistema y qué rol lleva cada uno.
 *
 * Cada módulo se parte en **ver** y **gestionar**. Antes había un solo permiso
 * por módulo —`gestionar pacientes` servía de las dos cosas—, así que un rol de
 * solo consulta como `pruebas` no podía entrar a mirar sin que le diéramos
 * también escritura, y las rutas terminaron preguntando por rol en vez de por
 * permiso.
 *
 * Partido así, los roles se suman de verdad: un administrador que además atiende
 * lleva también el rol de terapeuta y suma sus permisos, sin que haya que
 * inventar un rol nuevo.
 *
 * La matriz la revisó y corrigió Diana el 18 de septiembre de 2026; queda en
 * `Notas/Evaluaciones/Matriz_de_permisos_caine.xlsx`.
 *
 * Ojo: un permiso dice **a qué pantalla se entra**, no **cuánto se ve adentro**.
 * El alcance lo resuelve cada controlador — el encargado ve solo las citas de
 * sus hijos, el auxiliar solo los cobros de las citas que atiende.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** Permiso => qué habilita. El comentario es el que se ve en la matriz. */
    private const PERMISOS = [
        'ver pacientes' => 'Entrar al módulo y a la ficha del niño',
        'gestionar pacientes' => 'Crear, editar y eliminar pacientes',

        'ver expedientes' => 'Consultar expedientes y anamnesis',
        'gestionar expedientes' => 'Crear y editar expedientes y anamnesis',

        'ver agenda' => 'Ver el calendario',
        'agendar citas' => 'Crear, mover y reprogramar citas',

        'ver evaluaciones' => 'Consultar evaluaciones y objetivos',
        'gestionar evaluaciones' => 'Aplicar evaluaciones y escribir objetivos',

        'ver pagos' => 'Entrar a la pantalla de cobros',
        'registrar pagos' => 'Cobrar una sesión o un paquete',
        'autorizar pagos' => 'Dar el visto bueno al cobro de un auxiliar',

        'ver personas' => 'Consultar terapeutas, encargados y administrativos',
        'gestionar personas' => 'Crear y editar personas',

        'ver usuarios' => 'Entrar a la pantalla de usuarios',
        'gestionar usuarios' => 'Crear y editar usuarios',
        'asignar roles' => 'Abrir el modal de roles de una persona',

        'ver parametros' => 'Consultar los catálogos',
        'gestionar parametros' => 'Editar los catálogos',

        'ver programas' => 'Consultar los paquetes asignados',
        'gestionar programas' => 'Asignar y renovar paquetes',

        'ver reportes' => 'Entrar al módulo de informes',
        'ver indicadores' => 'Grafo de diagnósticos y áreas deficientes',
        'ver ocupacion personal' => 'Citas por día y comparación de horas',

        'acceso portal padres' => 'Módulo Kids: los hijos del encargado',
    ];

    private const MATRIZ = [
        // Ve todo, y gestiona la operación: roles, parámetros y pagos.
        'administrador' => [
            'ver pacientes', 'gestionar pacientes',
            'ver expedientes', 'gestionar expedientes',
            'ver agenda',
            'ver evaluaciones',
            'ver pagos', 'registrar pagos', 'autorizar pagos',
            'ver personas', 'gestionar personas',
            'ver usuarios', 'asignar roles',
            'ver parametros', 'gestionar parametros',
            'ver programas',
            'ver reportes', 'ver indicadores', 'ver ocupacion personal',
        ],

        // Lleva la operación completa: cobros, personas, expedientes, catálogos.
        'coordinador' => [
            'ver pacientes', 'gestionar pacientes',
            'ver expedientes', 'gestionar expedientes',
            'ver agenda', 'agendar citas',
            'ver evaluaciones',
            'ver pagos', 'registrar pagos', 'autorizar pagos',
            'ver personas', 'gestionar personas',
            'ver usuarios', 'gestionar usuarios', 'asignar roles',
            'ver parametros', 'gestionar parametros',
            'ver programas', 'gestionar programas',
            'ver reportes', 'ver indicadores', 'ver ocupacion personal',
        ],

        // Atiende en sucursal: registra sus pacientes y cobra sus sesiones. El
        // cobro que registra queda esperando autorización.
        'auxiliar' => [
            'ver pacientes', 'gestionar pacientes',
            'ver expedientes', 'gestionar expedientes',
            'ver agenda', 'agendar citas',
            'ver evaluaciones',
            'ver pagos', 'registrar pagos',
            'ver usuarios', 'gestionar usuarios',
            'ver programas',
            'ver indicadores',
        ],

        // Atiende, evalúa y escribe objetivos. No cobra ni agenda.
        'terapeuta' => [
            'ver pacientes',
            'ver expedientes',
            'ver agenda',
            'ver evaluaciones', 'gestionar evaluaciones',
            'ver programas',
        ],

        // Solo lo de sus hijos, y filtrado por el controlador.
        'encargado' => [
            'acceso portal padres',
            'ver agenda',
            'ver evaluaciones',
        ],

        // Ve todo, no toca nada: lleva los "ver" y ningún "gestionar".
        'pruebas' => [
            'ver pacientes',
            'ver expedientes',
            'ver agenda',
            'ver evaluaciones',
            'ver pagos',
            'ver personas',
            'ver usuarios',
            'ver parametros',
            'ver programas',
            'ver reportes', 'ver indicadores', 'ver ocupacion personal',
        ],
    ];

    public function run(): void
    {
        foreach (array_keys(self::PERMISOS) as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Los permisos viejos que ya no usa nadie. Se borran para que no queden
        // colgando en la tabla dando la impresión de que algo los revisa.
        Permission::whereNotIn('name', array_keys(self::PERMISOS))->delete();

        // syncPermissions y no givePermissionTo: deja el rol exactamente con lo
        // que dice la matriz, así quitar un permiso también surte efecto al
        // volver a sembrar.
        foreach (self::MATRIZ as $rol => $permisos) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web'])
                ->syncPermissions($permisos);
        }
    }
}
