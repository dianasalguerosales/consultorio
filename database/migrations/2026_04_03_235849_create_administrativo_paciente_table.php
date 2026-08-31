<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrativo_paciente', function (Blueprint $table) {
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->foreignId('administrativo_id')->constrained()->onDelete('cascade');
            $table->unique(['administrativo_id', 'paciente_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrativo_paciente');
    }
};
