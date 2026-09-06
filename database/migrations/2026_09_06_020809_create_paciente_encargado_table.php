<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('encargado_paciente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encargado_id')->constrained('encargados')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['encargado_id', 'paciente_id']); 
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_encargado');
    }
};
