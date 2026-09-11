<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asignaciones_programa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('programa_id')->constrained('programas')->restrictOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();

            $table->nullableMorphs('atendido_por');

            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('tipo_cita_id')->nullable()->constrained('tipo_citas')->nullOnDelete();

            $table->decimal('precio', 10, 2);
            $table->unsignedSmallInteger('cantidad_citas');

            // Días en formato ISO: 1 lunes ... 7 domingo.
            $table->json('dias');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->date('fecha_inicio');
            $table->string('estado')->default('activo');

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('asignacion_programa_id')->nullable()->after('programa_id')
                ->constrained('asignaciones_programa')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asignacion_programa_id');
        });

        Schema::dropIfExists('asignaciones_programa');
    }
};
