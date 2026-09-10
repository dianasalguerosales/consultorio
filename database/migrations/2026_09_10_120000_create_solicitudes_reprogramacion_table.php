<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes_reprogramacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();

            // Quién la pidió: el usuario del encargado.
            $table->foreignId('solicitada_por')->constrained('users')->cascadeOnDelete();
            $table->text('motivo')->nullable();

            // Los tres estados son fijos, así que van como texto y no como
            // catálogo: no hay nada que el administrador vaya a editar.
            $table->string('estado')->default('pendiente');

            // Quién la resolvió y cuándo. Nulos mientras está pendiente.
            $table->foreignId('resuelta_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelta_en')->nullable();
            $table->text('respuesta')->nullable();

            // Al aceptar se edita la cita existente, así que su fecha original
            // se guarda acá: es el único rastro de desde cuándo se movió.
            $table->date('fecha_original')->nullable();
            $table->time('hora_original')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Una sola solicitud pendiente por cita: el índice ayuda a buscarla.
            $table->index(['cita_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reprogramacion');
    }
};
