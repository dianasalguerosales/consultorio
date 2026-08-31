<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('encargado_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('terapeuta_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('servicio_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('programa_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('estado_cita_id')->nullable()->constrained('estado_citas');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('modalidad')->nullable();
            $table->decimal('precio_aplicado', 8, 2)->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('confirmada_por_encargado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
