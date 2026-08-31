<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->string('id')->primary(); // Código KID-2026001
            $table->string('nombre_pila')->nullable();
            $table->date('fecha_apertura')->nullable();
            $table->string('estado')->default('pendiente');
            $table->text('motivo_consulta')->nullable();
            $table->string('modalidad')->nullable();
            $table->foreignId('diagnostico_id')->nullable()->constrained('diagnosticos');
            $table->foreignId('escolaridad_id')->nullable()->constrained('escolaridades');
            $table->foreignId('anamnesis_id')->nullable()->constrained('anamnesis');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->onDelete('set null');
            $table->foreignId('creado_por_usuario_id')->nullable()->constrained('users');
            $table->text('observaciones_administrativas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
