<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los objetivos terapéuticos del niño, uno por renglón y agrupados por área.
 *
 * Cuelgan del paciente y no del expediente: el expediente es el documento de
 * ingreso, y los objetivos se van agregando y cerrando durante todo el
 * tratamiento.
 *
 * `area` guarda el mismo texto que `criterios.area`, que es la unidad clínica
 * con la que ya trabajan la anamnesis y /indicadores. Se deja como string y no
 * como FK a un catálogo aparte para no duplicar esa lista: lo que la anamnesis
 * marca en Observación es justo donde se ponen objetivos.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('objetivos_terapeuticos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();

            $table->string('area');
            $table->text('descripcion');

            // Quién lo planteó. Nullable porque un objetivo puede venir de una
            // reunión de equipo y no de un terapeuta en particular.
            $table->foreignId('terapeuta_id')->nullable()->constrained('terapeutas')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Se listan siempre por paciente y ordenados por área.
            $table->index(['paciente_id', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objetivos_terapeuticos');
    }
};
