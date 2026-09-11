<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('numero_autorizacion')->nullable()->after('metodo');
            $table->foreignId('registrado_por')->nullable()->after('fecha')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrado_por');
            $table->dropColumn('numero_autorizacion');
        });
    }
};
