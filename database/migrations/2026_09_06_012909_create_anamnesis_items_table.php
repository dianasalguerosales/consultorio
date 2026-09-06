<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('anamnesis_items', function (Blueprint $table) {
            $table->id();

            // Relación con anamnesis
            $table->foreignId('anamnesis_id')->constrained('anamnesis')->onDelete('cascade');

            // Relación con criterio
            $table->foreignId('criterio_id')->constrained('criterios')->onDelete('cascade');

            // Respuesta (1 = Necesita observación, 2 = En desarrollo, 3 = Adecuado)
            $table->tinyInteger('respuesta');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anamnesis_items');
    }
};
