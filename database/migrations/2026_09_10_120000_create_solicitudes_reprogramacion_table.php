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
            $table->foreignId('solicitada_por')->constrained('users')->cascadeOnDelete();
            $table->text('motivo')->nullable();
            $table->string('estado')->default('pendiente');

            $table->foreignId('resuelta_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelta_en')->nullable();
            $table->text('respuesta')->nullable();

            $table->date('fecha_original')->nullable();
            $table->time('hora_original')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['cita_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reprogramacion');
    }
};
