<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
<<<<<<< Updated upstream
use App\Http\Controllers\WompiController;
=======
use App\Http\Controllers\Admin\ActividadController;
>>>>>>> Stashed changes

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/actividades/{slug}', [WelcomeController::class, 'show'])
    ->name('activities.show');

// Rutas de autenticación
require __DIR__ . '/auth.php';

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/wompi/prueba', [WompiController::class, 'prueba'])
    ->middleware('auth')
    ->name('wompi.prueba');

// Administradores
Route::middleware(['auth', 'rol:superadmin,admin', 'user.status'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // CRUD de Actividades
    Route::resource('actividades', \App\Http\Controllers\Admin\ActividadController::class)
        ->parameters(['actividades' => 'actividad']);

    Route::get('actividades/{actividad}/configurar', [\App\Http\Controllers\Admin\ActividadConfiguracionController::class, 'show'])
        ->name('actividades.configurar');

    Route::post('actividades/{actividad}/items', [\App\Http\Controllers\Admin\ActividadController::class, 'guardarItem'])
        ->name('actividades.items.store');

    Route::put('actividades/{actividad}/items/{item}', [\App\Http\Controllers\Admin\ActividadController::class, 'actualizarItem'])
        ->name('actividades.items.update');

    Route::delete('actividades/{actividad}/items/{item}', [\App\Http\Controllers\Admin\ActividadController::class, 'eliminarItem'])
        ->name('actividades.items.destroy');

    Route::post('/actividades/{actividad}/productos/configuracion', [\App\Http\Controllers\Admin\ActividadConfiguracionController::class, 'guardarConfiguracionProductos'])
        ->name('actividades.productos.configuracion');
    
    Route::post('/actividades/{actividad}/enviar-revision', [ActividadController::class, 'enviarRevision'])
        ->name('actividades.enviar-revision');

    Route::post('/actividades/{actividad}/retirar-revision', [ActividadController::class, 'retirarRevision'])
    ->name('actividades.retirar-revision');

    // Datos que se solicitarán al comprador
    Route::post('actividades/{actividad}/items/{item}/datos-pedido', [\App\Http\Controllers\Admin\ActividadConfiguracionController::class, 'guardarDatosPedido'])
        ->name('actividades.datos-pedido.store');

    // Aumentos de precio y costo según las opciones seleccionadas
    Route::post('actividades/{actividad}/items/{item}/ajustes-precio', [\App\Http\Controllers\Admin\ActividadConfiguracionController::class, 'guardarAjustesPrecio'])
        ->name('actividades.ajustes-precio.store');

    // Fechas, horarios y turnos de la actividad
    Route::post('actividades/{actividad}/sesiones/configuracion', [\App\Http\Controllers\Admin\ActividadConfiguracionController::class, 'guardarSesiones'])
        ->name('actividades.sesiones.configuracion');

    // Rutas para la gestión de usuarios baneados y restauración
    Route::get('/users/banned', [\App\Http\Controllers\Admin\UserController::class, 'banned'])
        ->name('users.banned');

    // Rutas para la gestión de usuarios eliminados y restauración
    Route::get('/users/deleted', [\App\Http\Controllers\Admin\UserController::class, 'deleted'])
        ->name('users.deleted');

    Route::patch('/users/{user}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore'])
        ->name('users.restore');

    // Ruta para búsqueda en tiempo real (devuelve JSON)
    Route::get('/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search'])
        ->name('users.search');

    // Ruta para cambiar el estado (activo/inactivo)
    Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggleEstado'])
        ->name('users.toggle');

    // CRUD de Usuarios (Ahora sí generará admin.users.index, admin.users.create, etc.)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // ==========================================
    // CRUD DE ROLES (Solo Superadmin)
    // ==========================================
    Route::middleware('rol:superadmin')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RolController::class, 'index'])->name('index');
        Route::get('/crear', [\App\Http\Controllers\Admin\RolController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\RolController::class, 'store'])->name('store');
        Route::get('/{rol}/editar', [\App\Http\Controllers\Admin\RolController::class, 'edit'])->name('edit');
        Route::put('/{rol}', [\App\Http\Controllers\Admin\RolController::class, 'update'])->name('update');
        Route::delete('/{rol}', [\App\Http\Controllers\Admin\RolController::class, 'destroy'])->name('destroy');
    });
});

// Usuario normal (Protegido)
Route::middleware(['auth', 'rol:usuario', 'user.status'])->group(function () {
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

    // Nueva ruta para el formulario de onboarding opcional
    Route::get('/datos-personales', function () {
        return view('auth.datos-personales');
    })->name('datos.personales');

    Route::get('/omitir-perfil', function () {
        return redirect()->route('user.dashboard')
            ->with('success', 'Has omitido el registro. Puedes completar tus datos más tarde desde tu perfil.');
    })->name('perfil.omitir');

    Route::post('/datos-personales', [GoogleController::class, 'guardarDatosPersonales'])->name('perfil.guardar');
});

// Autenticación con Google (Público)
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::get('/vincular-cuenta', [GoogleController::class, 'showLinkAccountForm'])->name('vincular.cuenta');
Route::post('/vincular-cuenta', [GoogleController::class, 'linkAccount'])->name('vincular.cuenta.procesar');