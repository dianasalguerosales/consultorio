<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién registró al paciente.
 *
 * Hace falta para responder "sus pacientes" en el caso del auxiliar, que ve y
 * gestiona solo los suyos. Con las citas no alcanza: un niño que acaba de
 * registrar todavía no tiene ninguna, y sin esta columna no sería suyo hasta
 * agendarle algo.
 *
 * Queda nullable porque los pacientes que ya existen no tienen cómo saberlo:
 * nadie lo guardaba. Esos se resuelven por el otro lado — quien los atiende.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->foreignId('creado_por')->nullable()->after('encargado_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropColumn('creado_por');
        });
    }
};
