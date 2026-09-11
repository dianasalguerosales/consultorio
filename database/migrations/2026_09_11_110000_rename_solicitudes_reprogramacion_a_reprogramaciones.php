<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('solicitudes_reprogramacion', 'reprogramaciones');
    }

    public function down(): void
    {
        Schema::rename('reprogramaciones', 'solicitudes_reprogramacion');
    }
};
