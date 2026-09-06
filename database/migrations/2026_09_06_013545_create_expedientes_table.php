<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();

            // Datos personales
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();

            // Relación con paciente
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->onDelete('set null');
            $table->foreignId('anamnesis_id')->nullable()->constrained('anamnesis')->nullOnDelete();

            // Relaciones con catálogos
            $table->foreignId('diagnostico_id')->nullable()->constrained('diagnosticos')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('estado_expediente_id')->nullable()->constrained('estado_expedientes')->nullOnDelete();

            // Información clínica
            $table->string('codigo')->unique();
            $table->text('motivo_consulta')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->boolean('consentimiento')->default(false);
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};