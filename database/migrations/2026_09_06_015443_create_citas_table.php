<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            // Relación con paciente
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');

            // Relación con terapeuta
            $table->foreignId('terapeuta_id')->constrained('terapeutas')->onDelete('cascade');

            // Relación con catálogos
            $table->foreignId('estado_cita_id')->nullable()->constrained('estado_citas')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('tipo_cita_id')->nullable()->constrained('tipo_citas')->nullOnDelete();

            // Relación con servicio y programa
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();

            // Información propia de la cita
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->decimal('precio_aplicado', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
