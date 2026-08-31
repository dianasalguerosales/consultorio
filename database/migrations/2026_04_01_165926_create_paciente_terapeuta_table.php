<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_terapeuta', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('terapeuta_id')->constrained()->onDelete('cascade');
            $table->unique(['paciente_id', 'terapeuta_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_terapeuta');
    }
};
