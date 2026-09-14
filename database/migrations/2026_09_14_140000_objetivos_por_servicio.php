<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El área de un objetivo es el servicio (la terapia), no el área de la
 * anamnesis.
 *
 * La primera versión de la tabla guardaba `area` como texto, tomándolo de
 * `criterios.area`. Diana corrigió que los objetivos se ingresan por servicio:
 * de tres a cuatro por servicio y por niño.
 *
 * La tabla se rehace en vez de alterarse porque nació en esta misma tanda de
 * cambios y todavía no tiene datos — y porque SQLite no deja agregar una
 * columna NOT NULL sin valor por omisión a una tabla que ya existe. Si llegara
 * a tener filas, la migración se detiene en vez de borrarlas.
 */
return new class extends Migration {
    private const TABLA = 'objetivos_terapeuticos';

    public function up(): void
    {
        $this->abortarSiHayDatos();

        Schema::dropIfExists(self::TABLA);

        Schema::create(self::TABLA, function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();

            // El servicio es el área: los objetivos se plantean por terapia.
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();

            $table->text('descripcion');

            // Quién lo planteó. Nullable porque un objetivo puede venir de una
            // reunión de equipo y no de un terapeuta en particular.
            $table->foreignId('terapeuta_id')->nullable()->constrained('terapeutas')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Se listan siempre por niño y terapia.
            $table->index(['paciente_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        $this->abortarSiHayDatos();

        Schema::dropIfExists(self::TABLA);

        Schema::create(self::TABLA, function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->string('area');
            $table->text('descripcion');
            $table->foreignId('terapeuta_id')->nullable()->constrained('terapeutas')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['paciente_id', 'area']);
        });
    }

    private function abortarSiHayDatos(): void
    {
        if (! Schema::hasTable(self::TABLA)) {
            return;
        }

        $filas = DB::table(self::TABLA)->count();

        if ($filas > 0) {
            throw new RuntimeException(
                "`" . self::TABLA . "` tiene {$filas} filas. Esta migración rehace la tabla: "
                . 'respalde y vacíe los objetivos antes de correrla.'
            );
        }
    }
};
