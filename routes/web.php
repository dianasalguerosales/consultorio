<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\AnamnesisController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\EscolaridadController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\GeneroController;

use App\Models\Servicio;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notificaciones.index');

    // Configuración
    Route::get('/configuracion', [ProfileController::class, 'configuracion'])->name('configuracion');
    Route::put('/configuracion/password', [ProfileController::class, 'updatePassword'])->name('configuracion.password.update');

    Route::get('/generos', [GeneroController::class, 'list'])->name('generos.list');

    // Indicadores: analítica de la población de pacientes. Administrador y
    // coordinador, igual que la ocupación de personal.
    Route::get('/indicadores', [IndicadoresController::class, 'index'])
        ->middleware('permission:ver indicadores')
        ->name('indicadores.index');

    // Agenda: todos los roles con 'ver agenda' entran al calendario, pero el
    // controlador limita qué citas ve cada uno. Crear y editar exige además
    // 'agendar citas' (administrador, coordinador y auxiliar).
    Route::middleware(['permission:ver agenda'])->group(function () {
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');

        // Ocupación del personal: solo administrador y coordinador.
        Route::get('/agenda/ocupacion', [AgendaController::class, 'ocupacion'])
            ->middleware('permission:ver ocupacion personal')
            ->name('agenda.ocupacion');

        Route::middleware(['permission:agendar citas'])->group(function () {
            Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
            Route::put('/agenda/{cita}', [AgendaController::class, 'update'])->name('agenda.update');
            Route::delete('/agenda/{cita}', [AgendaController::class, 'destroy'])->name('agenda.destroy');
        });
    });

    // Pacientes: terapeuta y coordinador
    Route::middleware(['permission:gestionar pacientes'])->group(function () {
        Route::get('/pacientes', [PacientesController::class, 'index'])->name('pacientes.index');
        Route::post('/pacientes', [PacientesController::class, 'store'])->name('pacientes.store');
        Route::put('/pacientes/{paciente}', [PacientesController::class, 'update'])->name('pacientes.update');
        Route::delete('/pacientes/{paciente}', [PacientesController::class, 'destroy'])->name('pacientes.destroy');

        Route::get('/pacientes/{paciente}/expediente', [PacientesController::class, 'expediente'])->name('pacientes.expediente');
        Route::get('/pacientes/{paciente}/historial', [PacientesController::class, 'historial'])->name('pacientes.historial');
        Route::get('/pacientes/{paciente}/observaciones', [PacientesController::class, 'observaciones'])->name('pacientes.observaciones');
        Route::get('/pacientes/{paciente}/seguimiento', [PacientesController::class, 'seguimiento'])->name('pacientes.seguimiento');
    });

    // Expedientes: administrador y coordinador
    Route::middleware(['role:administrador|coordinador'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');

        // Personas: administrador y coordinador
        Route::middleware(['role:administrador|coordinador'])->group(function () {
            Route::get('/personas', [PersonasController::class, 'index'])->name('personas.index');
            // Administrativos
            Route::get('/personas/administrativos', [PersonasController::class, 'administrativos'])->name('personas.administrativos');
            Route::get('/personas/administrativos/create', [PersonasController::class, 'createAdministrativo'])->name('personas.administrativos.create');
            Route::post('/personas/administrativos', [PersonasController::class, 'storeAdministrativo'])->name('personas.administrativos.store');
            Route::get('/personas/administrativos/{id}', [PersonasController::class, 'showAdministrativo'])->name('personas.administrativos.show');
            Route::get('/personas/administrativos/{id}/edit', [PersonasController::class, 'editAdministrativo'])->name('personas.administrativos.edit');
            Route::put('/personas/administrativos/{id}', [PersonasController::class, 'updateAdministrativo'])->name('personas.administrativos.update');
            Route::delete('/personas/administrativos/{id}', [PersonasController::class, 'destroyAdministrativo'])->name('personas.administrativos.destroy');

            // Terapeutas
            Route::get('/personas/terapeutas', [PersonasController::class, 'terapeutas'])->name('personas.terapeutas');
            Route::get('/personas/terapeutas/create', [PersonasController::class, 'createTerapeuta'])->name('personas.terapeutas.create');
            Route::post('/personas/terapeutas', [PersonasController::class, 'storeTerapeuta'])->name('personas.terapeutas.store');
            Route::get('/personas/terapeutas/{id}', [PersonasController::class, 'showTerapeuta'])->name('personas.terapeutas.show');
            Route::get('/personas/terapeutas/{id}/edit', [PersonasController::class, 'editTerapeuta'])->name('personas.terapeutas.edit');
            Route::put('/personas/terapeutas/{id}', [PersonasController::class, 'updateTerapeuta'])->name('personas.terapeutas.update');
            Route::delete('/personas/terapeutas/{id}', [PersonasController::class, 'destroyTerapeuta'])->name('personas.terapeutas.destroy');

            // Encargados
            Route::get('/personas/encargados', [PersonasController::class, 'encargados'])->name('personas.encargados');
            Route::get('/personas/encargados/create', [PersonasController::class, 'createEncargado'])->name('personas.encargados.create');
            Route::post('/personas/encargados', [PersonasController::class, 'storeEncargado'])->name('personas.encargados.store');
            Route::get('/personas/encargados/{id}', [PersonasController::class, 'showEncargado'])->name('personas.encargados.show');
            Route::get('/personas/encargados/{id}/edit', [PersonasController::class, 'editEncargado'])->name('personas.encargados.edit');
            Route::put('/personas/encargados/{id}', [PersonasController::class, 'updateEncargado'])->name('personas.encargados.update');
            Route::delete('/personas/encargados/{id}', [PersonasController::class, 'destroyEncargado'])->name('personas.encargados.destroy');
        });


        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::post('/expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
        Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])->name('expedientes.update');
        Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy'])->name('expedientes.destroy');

        Route::post('/anamnesis', [AnamnesisController::class, 'store'])->name('anamnesis.store');
        Route::put('/anamnesis/{anamnesis}', [AnamnesisController::class, 'update'])->name('anamnesis.update');

        Route::get('/programas', [ProgramaController::class, 'index'])->name('programas.index');



        Route::get('/especialidades', [EspecialidadController::class, 'index'])->name('especialidades.index');
        Route::post('/especialidades', [EspecialidadController::class, 'store'])->name('especialidades.store');
        Route::put('/especialidades/{especialidad}', [EspecialidadController::class, 'update'])->name('especialidades.update');
        Route::delete('/especialidades/{especialidad}', [EspecialidadController::class, 'destroy'])->name('especialidades.destroy');

        Route::get('/parametros', [ParametroController::class, 'index'])->name('parametros.index');

        Route::resource('servicios', ServicioController::class)->only(['index','store','update','destroy']);
        Route::resource('especialidades', EspecialidadController::class)->only(['store','update','destroy']);
        Route::resource('escolaridades', EscolaridadController::class)->only(['store','update','destroy']);

    });
});

require __DIR__ . '/auth.php';