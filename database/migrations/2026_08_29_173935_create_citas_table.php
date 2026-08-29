<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('encargado_id')->nullable()->constrained('encargados');
            $table->foreignId('terapeuta_id')->constrained('terapeutas');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('programa_id')->nullable()->constrained('programas');
            $table->foreignId('estado_cita_id')->constrained('estado_citas');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('modalidad', 30)->nullable();
            $table->decimal('precio_aplicado', 10, 2)->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('observaciones')->nullable();
            $table->dateTime('confirmada_por_encargado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
