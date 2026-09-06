<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expediente_diagnostico', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->foreignId('diagnostico_id')->constrained('diagnosticos')->onDelete('cascade');

            $table->timestamps();
            $table->unique(['expediente_id', 'diagnostico_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_diagnostico');
    }
};
