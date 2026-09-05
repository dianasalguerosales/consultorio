<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('terapeutas', function (Blueprint $table) {
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
            $table->phone('telefono')->nullable();
            $table->string('correo')->nullable();

            // Información profesional
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
        Schema::dropIfExists('terapeutas');
    }
};
