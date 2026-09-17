<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El color con que cada terapia se pinta en la agenda.
 *
 * Antes salía de una paleta fija repartida por id dentro de `Pages/Agenda.vue`:
 * para cambiar un color había que tocar el código. Ahora se elige en Parámetros.
 *
 * Queda nullable a propósito: el servicio al que no le eligieron color sigue
 * tomando su tono de esa paleta, así nada cambia de aspecto mientras no lo
 * definan. Son siete caracteres porque se guarda como `#RRGGBB`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
