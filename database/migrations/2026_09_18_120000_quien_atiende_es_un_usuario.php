<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quien atiende una cita pasa a ser un usuario, no una fila de `terapeutas` o
 * de `administrativos`.
 *
 * Antes era una relación polimórfica: la cita guardaba el id y además la clase
 * (`App\Models\Terapeuta`), porque quien atiende podía estar en dos tablas
 * distintas. Eso tenía dos problemas:
 *
 * 1. La base quedaba amarrada al código: mover o renombrar la clase dejaba las
 *    citas apuntando a algo inexistente, y en silencio.
 * 2. Obligaba a decidir "¿es terapeuta o es administrativo?" cuando lo que de
 *    verdad importa es "¿tiene un rol que atiende?". Un administrador que
 *    además atiende necesitaba una ficha de terapeuta duplicada; ahora le basta
 *    con llevar los dos roles.
 *
 * Se puede traducir sin perder nada porque todas las personas que atienden
 * tienen usuario. Si alguna no lo tuviera, su cita quedaría sin quien la
 * atiende, así que la migración se detiene antes de tocar nada.
 */
return new class extends Migration
{
    /** Tabla => columna donde queda el usuario. */
    private const TABLAS = [
        'citas' => 'atiende_user_id',
        'asignaciones_programa' => 'atiende_user_id',
    ];

    private const PERSONAS = [
        'App\Models\Terapeuta' => 'terapeutas',
        'App\Models\Administrativo' => 'administrativos',
    ];

    public function up(): void
    {
        $this->abortarSiAlguienNoTieneUsuario();

        foreach (self::TABLAS as $tabla => $columna) {
            // Cada paso se salta si ya está hecho: si la migración se corta a
            // medias, volver a correrla termina el trabajo en vez de reventar
            // con "duplicate column".
            if (! Schema::hasColumn($tabla, $columna)) {
                Schema::table($tabla, function (Blueprint $t) use ($columna) {
                    $t->foreignId($columna)->nullable()->after('id')->constrained('users')->nullOnDelete();
                });
            }

            foreach (self::PERSONAS as $clase => $tablaPersona) {
                DB::table($tabla)
                    ->where('atendido_por_type', $clase)
                    ->whereNotNull('atendido_por_id')
                    ->update([
                        $columna => DB::raw(
                            "(select user_id from {$tablaPersona} where {$tablaPersona}.id = {$tabla}.atendido_por_id)"
                        ),
                    ]);
            }

            // El índice sobre las columnas viejas va primero: mientras exista,
            // SQLite se niega a borrar la columna que indexa.
            $this->borrarIndicesViejos($tabla);

            // De una en una: SQLite no borra dos columnas en la misma pasada.
            foreach (['atendido_por_type', 'atendido_por_id'] as $vieja) {
                if (Schema::hasColumn($tabla, $vieja)) {
                    Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn($vieja));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLAS as $tabla => $columna) {
            Schema::table($tabla, function (Blueprint $t) {
                $t->string('atendido_por_type')->nullable();
                $t->unsignedBigInteger('atendido_por_id')->nullable();
            });

            foreach (self::PERSONAS as $clase => $tablaPersona) {
                DB::table($tabla)
                    ->whereIn($columna, DB::table($tablaPersona)->whereNotNull('user_id')->pluck('user_id'))
                    ->update([
                        'atendido_por_type' => $clase,
                        'atendido_por_id' => DB::raw(
                            "(select id from {$tablaPersona} where {$tablaPersona}.user_id = {$tabla}.{$columna})"
                        ),
                    ]);
            }

            Schema::table($tabla, function (Blueprint $t) use ($columna) {
                $t->dropForeign([$columna]);
                $t->dropColumn($columna);
            });
        }
    }

    /** Los índices que tocan las columnas viejas, con el nombre que tengan. */
    private function borrarIndicesViejos(string $tabla): void
    {
        foreach (Schema::getIndexes($tabla) as $indice) {
            if (array_intersect(['atendido_por_type', 'atendido_por_id'], $indice['columns'])) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropIndex($indice['name']));
            }
        }
    }

    /**
     * Sin usuario no hay a quién apuntar: la cita perdería a quien la atendió,
     * que es un dato del expediente. Mejor detenerse que traducir a medias.
     */
    private function abortarSiAlguienNoTieneUsuario(): void
    {
        foreach (self::TABLAS as $tabla => $columna) {
            foreach (self::PERSONAS as $clase => $tablaPersona) {
                $huerfanas = DB::table($tabla)
                    ->where('atendido_por_type', $clase)
                    ->whereNotNull('atendido_por_id')
                    ->whereIn('atendido_por_id', DB::table($tablaPersona)->whereNull('user_id')->pluck('id'))
                    ->count();

                if ($huerfanas > 0) {
                    throw new RuntimeException(
                        "{$huerfanas} filas de `{$tabla}` las atiende alguien de `{$tablaPersona}` sin usuario. "
                        . 'Asígnele un usuario a esa persona antes de correr esta migración.'
                    );
                }
            }
        }
    }
};
