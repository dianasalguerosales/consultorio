<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Datos personales
            $table->string('nombres');
            $table->string('apellidos');

            // Relaciones con catálogos
            $table->foreignId('escolaridad_id')->nullable()->constrained('escolaridades')->nullOnDelete();
            $table->foreignId('genero_id')->nullable()->constrained('generos')->nullOnDelete();

            // Relación con encargado
            $table->foreignId('encargado_id')->nullable()->constrained('encargados')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
