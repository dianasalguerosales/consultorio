<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('administrativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Datos personales
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('dpi')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();

            // Información laboral
            $table->foreignId('cargo_id')
                  ->nullable()
                  ->constrained('cargos')
                  ->nullOnDelete();
            $table->foreignId('especialidad_id')
                  ->nullable()
                  ->constrained('especialidades')
                  ->nullOnDelete();
            $table->text('experiencia')->nullable();
            $table->text('certificaciones')->nullable();
            $table->text('cursos')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrativos');
    }
};
