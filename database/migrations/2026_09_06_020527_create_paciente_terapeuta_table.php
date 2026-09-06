<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paciente_terapeuta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('terapeuta_id')->constrained('terapeutas')->onDelete('cascade');

            $table->timestamps();
            $table->unique(['paciente_id', 'terapeuta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_terapeuta');
    }
};
