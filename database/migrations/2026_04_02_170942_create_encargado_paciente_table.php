<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encargado_paciente', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('encargado_id')->constrained()->onDelete('cascade');
            $table->unique(['encargado_id', 'paciente_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encargado_paciente');
    }
};
