<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('expediente_diagnostico');
        Schema::create('expediente_diagnostico', function (Blueprint $table) {
            $table->string('expediente_id'); 
            $table->unsignedBigInteger('diagnostico_id');

            $table->foreign('expediente_id')
                ->references('id')
                ->on('expedientes')
                ->onDelete('cascade');

            $table->foreign('diagnostico_id')
                ->references('id')
                ->on('diagnosticos')
                ->onDelete('cascade');

            $table->primary(['expediente_id', 'diagnostico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_diagnostico');
    }
};