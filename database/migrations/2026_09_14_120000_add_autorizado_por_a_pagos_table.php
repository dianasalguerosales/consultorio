<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién autorizó el pago.
 *
 * Solo hace falta cuando lo registra un auxiliar: un administrador o un
 * coordinador cobran por sí mismos. Por eso la columna es nullable — que esté
 * vacía no significa lo mismo en un caso que en el otro, y quien lo distingue
 * es el rol de `registrado_por`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('autorizado_por')->nullable()->after('registrado_por')
                ->constrained('users')->nullOnDelete();

            $table->timestamp('autorizado_en')->nullable()->after('autorizado_por');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('autorizado_en');
            $table->dropConstrainedForeignId('autorizado_por');
        });
    }
};
