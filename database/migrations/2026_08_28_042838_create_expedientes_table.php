<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('paciente_id')->unique();
            $table->date('fecha_apertura');
            $table->string('estado', 30)->default('activo');
            $table->unsignedBigInteger('creado_por_usuario_id');
            $table->text('observaciones_administrativas')->nullable();
            $table->timestamps();

            $table->foreign('creado_por_usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
