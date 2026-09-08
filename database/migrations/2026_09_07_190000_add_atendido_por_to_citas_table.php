<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Una cita la puede atender una terapeuta o un auxiliar (que vive en la
     * tabla administrativos), porque los auxiliares atienden solos en sucursal.
     * Se reemplaza terapeuta_id por una relación polimórfica atendido_por.
     *
     * google_event_id guarda el id del evento en Google Calendar para poder
     * actualizarlo o cancelarlo después en vez de crear duplicados.
     *
     * Se recrea la tabla en lugar de usar dropForeign porque SQLite no soporta
     * eliminar llaves foráneas. Recrear funciona igual en SQLite y en MySQL.
     */
    public function up(): void
    {
        Schema::create('citas_nueva', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');

            // Terapeuta o Administrativo (auxiliar).
            $table->nullableMorphs('atendido_por');

            $table->foreignId('estado_cita_id')->nullable()->constrained('estado_citas')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('tipo_cita_id')->nullable()->constrained('tipo_citas')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->decimal('precio_aplicado', 8, 2)->nullable();

            $table->string('google_event_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // La agenda siempre consulta por rango de fechas.
            $table->index(['fecha', 'hora_inicio']);
        });

        // Las citas que existan pasan a ser atendidas por su terapeuta.
        DB::table('citas')->orderBy('id')->chunkById(200, function ($filas) {
            $nuevas = [];

            foreach ($filas as $cita) {
                $nuevas[] = [
                    'id' => $cita->id,
                    'paciente_id' => $cita->paciente_id,
                    'atendido_por_type' => $cita->terapeuta_id ? \App\Models\Terapeuta::class : null,
                    'atendido_por_id' => $cita->terapeuta_id,
                    'estado_cita_id' => $cita->estado_cita_id,
                    'modalidad_id' => $cita->modalidad_id,
                    'tipo_cita_id' => $cita->tipo_cita_id,
                    'servicio_id' => $cita->servicio_id,
                    'programa_id' => $cita->programa_id,
                    'fecha' => $cita->fecha,
                    'hora_inicio' => $cita->hora_inicio,
                    'hora_fin' => $cita->hora_fin,
                    'precio_aplicado' => $cita->precio_aplicado,
                    'google_event_id' => null,
                    'created_at' => $cita->created_at,
                    'updated_at' => $cita->updated_at,
                    'deleted_at' => $cita->deleted_at,
                ];
            }

            DB::table('citas_nueva')->insert($nuevas);
        });

        Schema::withoutForeignKeyConstraints(function () {
            Schema::drop('citas');
            Schema::rename('citas_nueva', 'citas');
        });
    }

    public function down(): void
    {
        Schema::create('citas_previa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('terapeuta_id')->constrained('terapeutas')->onDelete('cascade');
            $table->foreignId('estado_cita_id')->nullable()->constrained('estado_citas')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('tipo_cita_id')->nullable()->constrained('tipo_citas')->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->decimal('precio_aplicado', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Solo vuelven las citas de terapeutas: terapeuta_id no admite auxiliares.
        DB::table('citas')
            ->where('atendido_por_type', \App\Models\Terapeuta::class)
            ->orderBy('id')
            ->chunkById(200, function ($filas) {
                $previas = [];

                foreach ($filas as $cita) {
                    $previas[] = [
                        'id' => $cita->id,
                        'paciente_id' => $cita->paciente_id,
                        'terapeuta_id' => $cita->atendido_por_id,
                        'estado_cita_id' => $cita->estado_cita_id,
                        'modalidad_id' => $cita->modalidad_id,
                        'tipo_cita_id' => $cita->tipo_cita_id,
                        'servicio_id' => $cita->servicio_id,
                        'programa_id' => $cita->programa_id,
                        'fecha' => $cita->fecha,
                        'hora_inicio' => $cita->hora_inicio,
                        'hora_fin' => $cita->hora_fin,
                        'precio_aplicado' => $cita->precio_aplicado,
                        'created_at' => $cita->created_at,
                        'updated_at' => $cita->updated_at,
                        'deleted_at' => $cita->deleted_at,
                    ];
                }

                DB::table('citas_previa')->insert($previas);
            });

        Schema::withoutForeignKeyConstraints(function () {
            Schema::drop('citas');
            Schema::rename('citas_previa', 'citas');
        });
    }
};
