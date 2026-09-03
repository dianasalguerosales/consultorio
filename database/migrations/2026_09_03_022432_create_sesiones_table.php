<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained()->onDelete('cascade');
            $table->text('evolucion')->nullable();
            $table->text('observaciones_clinicas')->nullable();
            $table->text('observaciones_generales')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
