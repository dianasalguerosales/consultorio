<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('encargados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Datos personales
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('dpi')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('direccion')->nullable();

            // Información adicional
            $table->string('ocupacion')->nullable();
            $table->foreignId('relacion_paciente_id')->nullable()->constrained('relaciones_paciente')->nullOnDelete();
            $table->foreignId('genero_id')->nullable()->constrained('generos')->nullOnDelete();
            $table->foreignId('estado_civil_id')->nullable()->constrained('estados_civiles')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encargados');
    }
};
