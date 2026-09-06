<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();

            // Relación con cita
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');

            // Relación con terapeuta (quien atendió la sesión)
            $table->foreignId('terapeuta_id')->constrained('terapeutas')->onDelete('cascade');

            // Información clínica
            $table->text('evolucion')->nullable();
            $table->text('observaciones_clinicas')->nullable();
            $table->text('observaciones_generales')->nullable();

            // Duración real de la sesión (en minutos)
            $table->integer('duracion_minutos')->nullable();

            // Estado de la sesión
            $table->foreignId('estado_sesion_id')->nullable()->constrained('estado_sesiones')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
