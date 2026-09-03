<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anamnesis_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anamnesis_id')->constrained()->onDelete('cascade');
            $table->string('area'); // Desarrollo, Cognitiva, Emocional
            $table->string('criterio'); // Pregunta o ítem
            $table->tinyInteger('respuesta'); // 1, 2, 3
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anamnesis_items');
    }
};
