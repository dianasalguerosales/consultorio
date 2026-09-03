<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediente_evaluacion', function (Blueprint $table) {
            $table->string('expediente_id'); 
            $table->unsignedBigInteger('evaluacion_id');

            $table->foreign('expediente_id')
                ->references('id')
                ->on('expedientes')
                ->onDelete('cascade');

            $table->foreign('evaluacion_id')
                ->references('id')
                ->on('evaluaciones')
                ->onDelete('cascade');

            $table->primary(['expediente_id', 'evaluacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_evaluacion');
    }
};
