<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anamnesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->onDelete('cascade');
            $table->text('antecedentes_familiares')->nullable();
            $table->text('antecedentes_medicos')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anamnesis');
    }
};
