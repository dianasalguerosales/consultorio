<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('expediente_terapia');
        Schema::create('expediente_terapia', function (Blueprint $table) {
            $table->string('expediente_id');
            $table->unsignedBigInteger('terapia_id');

            $table->foreign('expediente_id')
                ->references('id')
                ->on('expedientes')
                ->onDelete('cascade');

            $table->foreign('terapia_id')
                ->references('id')
                ->on('terapias')
                ->onDelete('cascade');

            $table->primary(['expediente_id', 'terapia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_terapia');
    }
};


