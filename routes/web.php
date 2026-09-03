<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\AnamnesisController;
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

    // Usuarios: solo administrador y coordinador
    Route::middleware(['role:administrador|coordinador'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
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
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::post('/expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
        Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])->name('expedientes.update');

        Route::post('/anamnesis', [AnamnesisController::class, 'store'])->name('anamnesis.store');
        Route::put('/anamnesis/{anamnesis}', [AnamnesisController::class, 'update'])->name('anamnesis.update');
    });
});

require __DIR__.'/auth.php';