<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expediente_servicios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');

            $table->timestamps();
            $table->unique(['expediente_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_servicios');
    }
};
