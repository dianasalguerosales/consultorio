<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\OcupacionController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PacientesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\AsignacionProgramaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\AnamnesisController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\GeneroController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\SolicitudReprogramacionController;
use App\Http\Controllers\SubalternosController;
use App\Http\Controllers\HijosController;
use App\Http\Controllers\ObjetivoTerapeuticoController;
use App\Http\Controllers\EvaluacionesController;
use App\Http\Controllers\InformesController;
use App\Http\Controllers\TerapeutaController;
use App\Http\Controllers\EncargadoController;
use App\Http\Controllers\GoogleController;
use App\Models\Servicio;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/google/auth', [GoogleController::class, 'redirect'])->name('google.auth');
Route::get('/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notificaciones.index');
    Route::put('/notificaciones/{notificacion}/leer', [NotificationController::class, 'leer'])->name('notificaciones.leer');
    Route::put('/notificaciones/leer-todas', [NotificationController::class, 'leerTodas'])->name('notificaciones.leerTodas');

    // Configuración
    Route::get('/configuracion', [ProfileController::class, 'configuracion'])->name('configuracion');
    Route::put('/configuracion/password', [ProfileController::class, 'updatePassword'])->name('configuracion.password.update');

    Route::get('/generos', [GeneroController::class, 'list'])->name('generos.list');

    // Portal del encargado: sus hijos. El controlador filtra por encargado_id.
    Route::get('/hijos', [HijosController::class, 'index'])
        ->middleware('permission:acceso portal padres')
        ->name('hijos.index');

    // Ocupación del personal: solo administrador y coordinador. Vive bajo
    // /indicadores y no bajo /agenda para que el menú resalte Indicadores, que
    // es de donde se entra: el activo se calcula con startsWith sobre la URL.
    Route::get('/indicadores/ocupacion', [OcupacionController::class, 'index'])
        ->middleware('permission:ver ocupacion personal')
        ->name('indicadores.ocupacion');

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

        Route::middleware(['permission:agendar citas'])->group(function () {
            Route::post('/agenda', [AgendaController::class, 'store'])->name('agenda.store');
            Route::put('/agenda/{cita}', [AgendaController::class, 'update'])->name('agenda.update');
            Route::delete('/agenda/{cita}', [AgendaController::class, 'destroy'])->name('agenda.destroy');
        });

        // El encargado pide mover una cita de sus hijos; la resuelve
        // coordinación o administración.
        Route::post('/agenda/{cita}/reprogramacion', [SolicitudReprogramacionController::class, 'store'])
            ->middleware('permission:acceso portal padres')
            ->name('agenda.reprogramacion.store');

        Route::put('/reprogramaciones/{solicitud}', [SolicitudReprogramacionController::class, 'update'])
            ->middleware('role:administrador|coordinador')
            ->name('reprogramaciones.update');

        // El controlador verifica además que sea quien atiende la cita.
        Route::post('/agenda/{cita}/sesion', [SesionController::class, 'store'])
            ->middleware('role:terapeuta')
            ->name('agenda.sesion.store');
    });

    // Informes: cruzan a todos los pacientes, así que solo administrador,
    // coordinador y pruebas (los que tienen 'ver reportes').
    Route::middleware(['permission:ver reportes'])->group(function () {
        Route::get('/informes', [InformesController::class, 'index'])->name('informes.index');
        Route::get('/informes/exportar', [InformesController::class, 'exportar'])->name('informes.exportar');
    });

    // Evaluaciones: el récord de las aplicadas. Mismo permiso que pacientes,
    // que es el que ya tiene el terapeuta.
    // Consultar el récord es más amplio que aplicar: pruebas y encargado solo
    // consultan, y al encargado el controlador le filtra a sus hijos.
    Route::get('/evaluaciones', [EvaluacionesController::class, 'index'])
        ->middleware('permission:ver evaluaciones')
        ->name('evaluaciones.index');

    Route::post('/evaluaciones', [EvaluacionesController::class, 'store'])
        ->middleware('permission:gestionar pacientes')
        ->name('evaluaciones.store');

    // Objetivos terapéuticos: de 3 a 4 por terapia y por niño. Módulo aparte
    // del expediente, que se llena una vez al ingreso.
    Route::get('/objetivos', [ObjetivoTerapeuticoController::class, 'index'])
        ->middleware('permission:ver evaluaciones')
        ->name('objetivos.index');

    Route::middleware(['permission:gestionar pacientes'])->group(function () {
        Route::post('/objetivos', [ObjetivoTerapeuticoController::class, 'store'])->name('objetivos.store');
        Route::delete('/objetivos', [ObjetivoTerapeuticoController::class, 'destroy'])->name('objetivos.destroy');
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

    // Pagos: se cobra sobre las sesiones ya recibidas. 'pruebas' entra
    // solo a consultar, por eso no se le abre el registro.
    Route::middleware(['role:administrador|coordinador|auxiliar|pruebas'])->group(function () {
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    });

    Route::middleware(['role:administrador|coordinador|auxiliar'])->group(function () {
        Route::post('/pagos/{cita}', [PagoController::class, 'store'])->name('pagos.store');
        Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');

        // El cliente paga el programa completo; adentro se reparte entre sus
        // citas. Van antes que /pagos/{cita} no haría falta —el prefijo
        // 'paquetes' no choca— pero se agrupan acá por ser el mismo permiso.
        Route::post('/pagos/paquetes/{asignacion}', [PagoController::class, 'pagarPaquete'])->name('pagos.paquete');
        Route::delete('/pagos/paquetes/{asignacion}', [PagoController::class, 'anularPaquete'])->name('pagos.paquete.anular');
    });

    // Autorizar el paquete completo: mismo criterio que autorizar un cobro.
    Route::middleware(['role:administrador|coordinador'])->group(function () {
        Route::post('/pagos/paquetes/{asignacion}/autorizar', [PagoController::class, 'autorizarPaquete'])
            ->name('pagos.paquete.autorizar');
    });

    // Autorizar el cobro de un auxiliar: el auxiliar no se autoriza solo.
    Route::middleware(['role:administrador|coordinador'])->group(function () {
        Route::post('/pagos/{pago}/autorizar', [PagoController::class, 'autorizar'])->name('pagos.autorizar');
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
            // Cada tipo de persona tiene su controlador. Las URLs y los
            // nombres de ruta no cambian: el frontend usa las URLs literales.
            //
            // Se quitaron los 'create' y 'show', que apuntaban a métodos
            // inexistentes y nadie enlazaba.

            // Administrativos
            Route::get('/personas/administrativos', [AdministrativoController::class, 'index'])->name('personas.administrativos');
            Route::post('/personas/administrativos', [AdministrativoController::class, 'store'])->name('personas.administrativos.store');
            Route::get('/personas/administrativos/{id}/edit', [AdministrativoController::class, 'edit'])->name('personas.administrativos.edit');
            Route::put('/personas/administrativos/{id}', [AdministrativoController::class, 'update'])->name('personas.administrativos.update');
            Route::delete('/personas/administrativos/{id}', [AdministrativoController::class, 'destroy'])->name('personas.administrativos.destroy');

            // Roles de una persona: se le asignan a su usuario ligado.
            Route::put('/personas/{tipo}/{id}/roles', [RolesController::class, 'update'])->name('personas.roles.update');

            // Organigrama: los subalternos salen del cargo, no de una tabla.
            Route::get('/personas/administrativo/{administrativo}/subalternos', [SubalternosController::class, 'show'])->name('personas.subalternos');

            // Terapeutas
            Route::get('/personas/terapeutas', [TerapeutaController::class, 'index'])->name('personas.terapeutas');
            Route::post('/personas/terapeutas', [TerapeutaController::class, 'store'])->name('personas.terapeutas.store');
            Route::get('/personas/terapeutas/{id}/edit', [TerapeutaController::class, 'edit'])->name('personas.terapeutas.edit');
            Route::put('/personas/terapeutas/{id}', [TerapeutaController::class, 'update'])->name('personas.terapeutas.update');
            Route::delete('/personas/terapeutas/{id}', [TerapeutaController::class, 'destroy'])->name('personas.terapeutas.destroy');

            // Encargados
            Route::get('/personas/encargados', [EncargadoController::class, 'index'])->name('personas.encargados');
            Route::post('/personas/encargados', [EncargadoController::class, 'store'])->name('personas.encargados.store');
            Route::get('/personas/encargados/{id}/edit', [EncargadoController::class, 'edit'])->name('personas.encargados.edit');
            Route::put('/personas/encargados/{id}', [EncargadoController::class, 'update'])->name('personas.encargados.update');
            Route::delete('/personas/encargados/{id}', [EncargadoController::class, 'destroy'])->name('personas.encargados.destroy');



        });


        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::post('/expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
        Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])->name('expedientes.update');
        Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy'])->name('expedientes.destroy');

        Route::post('/anamnesis', [AnamnesisController::class, 'store'])->name('anamnesis.store');
        Route::put('/anamnesis/{anamnesis}', [AnamnesisController::class, 'update'])->name('anamnesis.update');

        // Programas de los niños. El catálogo de programas vive en
        // /parametros; acá se ve a quién se le asignó cada uno.
        Route::get('/programas', [AsignacionProgramaController::class, 'index'])->name('programas.index');
        Route::post('/pacientes/{paciente}/programa', [AsignacionProgramaController::class, 'store'])->name('programas.asignar');
        Route::delete('/programas/{asignacion}', [AsignacionProgramaController::class, 'destroy'])->name('programas.destroy');

        // Generar el paquete del mes siguiente. A mano y no en automático: lo
        // decide coordinación, por eso el rol es más estrecho que el del resto.
        Route::post('/programas/{asignacion}/renovar', [AsignacionProgramaController::class, 'renovar'])
            ->middleware('role:administrador|coordinador')
            ->name('programas.renovar');

        Route::get('/parametros', [ParametroController::class, 'index'])->name('parametros.index');

        // Un CRUD para los seis catálogos. La clave se valida contra
        // App\Catalogos\Catalogos, que es la lista blanca.
        Route::post('/catalogos/{catalogo}', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::put('/catalogos/{catalogo}/{id}', [CatalogoController::class, 'update'])->name('catalogos.update');
        Route::delete('/catalogos/{catalogo}/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');


        Route::get('/personas/terapeuta/{id}/pacientes', [TerapeutaController::class, 'pacientes'])->name('terapeuta.pacientes');
        Route::get('/personas/encargado/{id}/pacientes', [EncargadoController::class, 'pacientes'])->name('encargado.pacientes');

    });
});

require __DIR__ . '/auth.php';