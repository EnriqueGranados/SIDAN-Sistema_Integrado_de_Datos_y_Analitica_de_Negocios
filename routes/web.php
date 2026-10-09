<?php

use App\Http\Controllers\ActividadAccionController;
use App\Http\Controllers\Admin\ActividadConfiguracionController;
use App\Http\Controllers\Admin\ActividadController;
use App\Http\Controllers\Admin\ActividadPublicacionController;
use App\Http\Controllers\Admin\ActividadPromocionController;
use App\Http\Controllers\Admin\ActividadRevisionController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\EspacioController;
use App\Http\Controllers\Admin\EtiquetaController;
use App\Http\Controllers\Admin\RecursoController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WelcomeAdministracionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WompiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/actividades', [WelcomeController::class, 'catalogo'])
    ->name('activities.index');

Route::get('/actividades/sugerencias', [WelcomeController::class, 'sugerencias'])
    ->middleware('throttle:120,1')
    ->name('activities.suggestions');

Route::get('/actividades/{slug}', [WelcomeController::class, 'show'])
    ->name('activities.show');

require __DIR__.'/auth.php';

Route::get('/dashboard', function (Request $request) {
    $rol = $request->user()?->rol?->nombre;

    return match ($rol) {
        'admin', 'superadmin' => redirect()->route('admin.dashboard'),
        'usuario' => redirect()->route('user.dashboard'),
        default => redirect()
            ->route('welcome')
            ->with('error', 'No se pudo determinar el panel correspondiente a tu cuenta.'),
    };
})
    ->middleware(['auth', 'user.status'])
    ->name('dashboard');

Route::get('/actividades/{slug}/participar', [ActividadAccionController::class, 'participar'])
    ->name('activities.participar');

Route::get('/actividades/{slug}/comprar', [ActividadAccionController::class, 'comprar'])
    ->name('activities.comprar');


// Cambio de contraseña.
Route::middleware('auth')->group(function () {
    Route::get('/cambiar-password-obligatorio', [ForcePasswordChangeController::class, 'create',])->name('password.force.edit');
    Route::post('/cambiar-password-obligatorio', [ForcePasswordChangeController::class, 'store',])->name('password.force.update');
});

// Perfil
Route::middleware(['auth', 'force.password.change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/password/email', [ProfileController::class, 'sendPasswordLink'])->middleware('throttle:3,1')->name('profile.password.email');
});

// Ubicación
Route::middleware(['throttle:60,1'])->prefix('ubicaciones')->name('ubicaciones.')->group(function () {
    Route::get('/paises', [UbicacionController::class, 'paises'])->name('paises');
    Route::get('/estados/{pais}', [UbicacionController::class, 'estados'])->name('estados');
    Route::get('/ciudades/{pais}/{estado}', [UbicacionController::class, 'ciudades'])->name('ciudades');
});

Route::get('/wompi/prueba', [WompiController::class, 'prueba'])
    ->middleware('auth')
    ->name('wompi.prueba');

Route::get('/wompi/prueba/estado', [WompiController::class, 'estadoPrueba'])
    ->middleware('auth')
    ->name('wompi.prueba.estado');

Route::get('/wompi/prueba/resultado', [WompiController::class, 'resultado'])
    ->middleware('auth')
    ->name('wompi.prueba.resultado');

Route::get('/tunnel-test', function () {
    return response('SIDAN TUNNEL OK', 200);
});

Route::post('/wompi/webhook', [WompiController::class, 'webhook'])
    ->name('wompi.webhook');

// Administradores
Route::middleware(['auth', 'force.password.change', 'rol:superadmin,admin', 'user.status'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/welcome', [WelcomeAdministracionController::class, 'index'])
            ->name('welcome.index');

        Route::get('/welcome/actividades', [WelcomeAdministracionController::class, 'buscarActividades'])
            ->middleware('throttle:120,1')
            ->name('welcome.actividades.buscar');

        Route::post('/welcome/espacios', [WelcomeAdministracionController::class, 'crearEspacio'])
            ->name('welcome.espacios.store');

        Route::put('/welcome/destacada', [WelcomeAdministracionController::class, 'establecerDestacada'])
            ->name('welcome.destacada.actualizar');

        Route::delete('/welcome/destacada', [WelcomeAdministracionController::class, 'quitarDestacada'])
            ->name('welcome.destacada.quitar');

        Route::patch('/welcome/destacada/{actividad}/prioridad', [WelcomeAdministracionController::class, 'actualizarPrioridad'])
            ->name('welcome.destacada.prioridad');

        Route::put('/welcome/espacios/{espacio}/actividad', [WelcomeAdministracionController::class, 'asignar'])
            ->name('welcome.espacios.asignar');

        Route::delete('/welcome/espacios/{espacio}/actividad', [WelcomeAdministracionController::class, 'quitar'])
            ->name('welcome.espacios.quitar');

        Route::patch('/welcome/espacios/{espacio}/mover', [WelcomeAdministracionController::class, 'mover'])
            ->name('welcome.espacios.mover');

        Route::delete('/welcome/espacios/{espacio}', [WelcomeAdministracionController::class, 'eliminarEspacio'])
            ->name('welcome.espacios.destroy');

        Route::get('/revisiones-actividades', [ActividadRevisionController::class, 'index'])
            ->name('actividades.revision.index');

        Route::get('/revisiones-actividades/{actividad}', [ActividadRevisionController::class, 'show'])
            ->name('actividades.revision.show');

        Route::post('/revisiones-actividades/{actividad}/observaciones', [ActividadRevisionController::class, 'guardarObservacion'])
            ->name('actividades.revision.observaciones.store');

        Route::delete('/revisiones-actividades/{actividad}/observaciones/{observacion}', [ActividadRevisionController::class, 'eliminarObservacion'])
            ->name('actividades.revision.observaciones.destroy');

        Route::post('/revisiones-actividades/{actividad}/aprobar', [ActividadRevisionController::class, 'aprobar'])
            ->name('actividades.revision.aprobar');

        Route::post('/revisiones-actividades/{actividad}/solicitar-cambios', [ActividadRevisionController::class, 'solicitarCambios'])
            ->name('actividades.revision.solicitar-cambios');

        Route::post('/revisiones-actividades/{actividad}/rechazar', [ActividadRevisionController::class, 'rechazar'])
            ->name('actividades.revision.rechazar');

        Route::post('actividades/{actividad}/publicar', [ActividadPublicacionController::class, 'publicar'])
            ->name('actividades.publicar');

        Route::post('actividades/{actividad}/retirar-publicacion', [ActividadPublicacionController::class, 'retirar'])
            ->name('actividades.retirar-publicacion');

        Route::resource('actividades', ActividadController::class)
            ->parameters([
                'actividades' => 'actividad',
            ]);

        Route::get('actividades/{actividad}/configurar', [ActividadConfiguracionController::class, 'show'])
            ->name('actividades.configurar');

        Route::post('actividades/{actividad}/items', [ActividadController::class, 'guardarItem'])
            ->name('actividades.items.store');

        Route::put('actividades/{actividad}/items/{item}', [ActividadController::class, 'actualizarItem'])
            ->name('actividades.items.update');

        Route::delete('actividades/{actividad}/items/{item}', [ActividadController::class, 'eliminarItem'])
            ->name('actividades.items.destroy');

        Route::post('actividades/{actividad}/productos/configuracion', [ActividadConfiguracionController::class, 'guardarConfiguracionProductos'])
            ->name('actividades.productos.configuracion');

        Route::post('actividades/{actividad}/enviar-revision', [ActividadController::class, 'enviarRevision'])
            ->name('actividades.enviar-revision');

        Route::post('actividades/{actividad}/retirar-revision', [ActividadController::class, 'retirarRevision'])
            ->name('actividades.retirar-revision');

        Route::post('actividades/{actividad}/items/{item}/datos-pedido', [ActividadConfiguracionController::class, 'guardarDatosPedido'])
            ->name('actividades.datos-pedido.store');

        Route::post('actividades/{actividad}/items/{item}/ajustes-precio', [ActividadConfiguracionController::class, 'guardarAjustesPrecio'])
            ->name('actividades.ajustes-precio.store');

        Route::get('actividades/{actividad}/promociones', [ActividadPromocionController::class, 'index'])
            ->name('actividades.promociones.index');

        Route::post('actividades/{actividad}/promociones', [ActividadPromocionController::class, 'store'])
            ->name('actividades.promociones.store');

        Route::patch('actividades/{actividad}/promociones/{promocion}', [ActividadPromocionController::class, 'update'])
            ->name('actividades.promociones.update');

        Route::patch('actividades/{actividad}/promociones/{promocion}/estado', [ActividadPromocionController::class, 'toggle'])
            ->name('actividades.promociones.toggle');

        Route::delete('actividades/{actividad}/promociones/{promocion}', [ActividadPromocionController::class, 'destroy'])
            ->name('actividades.promociones.destroy');

        Route::post('actividades/{actividad}/sesiones/configuracion', [ActividadConfiguracionController::class, 'guardarSesiones'])
            ->name('actividades.sesiones.configuracion');

        Route::post('actividades/{actividad}/configuracion/finalizar', [ActividadConfiguracionController::class, 'finalizarConfiguracion'])
            ->name('actividades.configuracion.finalizar');

        Route::post('actividades/{actividad}/presentacion', [ActividadConfiguracionController::class, 'guardarPresentacion'])
            ->name('actividades.presentacion.store');

        Route::patch('actividades/{actividad}/medios/{medio}/portada', [ActividadConfiguracionController::class, 'establecerPortada'])
            ->name('actividades.medios.portada');

        Route::delete('actividades/{actividad}/medios/{medio}', [ActividadConfiguracionController::class, 'eliminarMedio'])
            ->name('actividades.medios.destroy');

        Route::patch('categorias/reordenar', [CategoriaController::class, 'reorder'])
            ->name('categorias.reorder');

        Route::resource('categorias', CategoriaController::class)
            ->except(['show'])
            ->parameters([
                'categorias' => 'categoria',
            ]);

        Route::resource('etiquetas', EtiquetaController::class)
            ->except(['show'])
            ->parameters([
                'etiquetas' => 'etiqueta',
            ]);

        Route::resource('recursos', RecursoController::class)
            ->except(['show'])
            ->parameters([
                'recursos' => 'recurso',
            ]);

        Route::get('espacios/buscar', [EspacioController::class, 'buscar'])
            ->name('espacios.buscar');

        Route::post('espacios/crear-rapido', [EspacioController::class, 'crearRapido'])
            ->name('espacios.crear-rapido');

        Route::get('espacios/{espacio}/descendientes', [EspacioController::class, 'descendientes'])
            ->name('espacios.descendientes');

        Route::resource('espacios', EspacioController::class)
            ->except(['show'])
            ->parameters([
                'espacios' => 'espacio',
            ]);

        Route::get('/users/banned', [UserController::class, 'banned'])
            ->name('users.banned');

        Route::get('/users/deleted', [UserController::class, 'deleted'])
            ->name('users.deleted');

        Route::get('/users/search', [UserController::class, 'search'])
            ->name('users.search');

        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])
            ->name('users.restore');

        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleEstado'])
            ->name('users.toggle');

        Route::resource('users', UserController::class);

        Route::middleware('rol:superadmin')
            ->prefix('roles')
            ->name('roles.')
            ->group(function () {
                Route::get('/', [RolController::class, 'index'])
                    ->name('index');

                Route::get('/crear', [RolController::class, 'create'])
                    ->name('create');

                Route::post('/', [RolController::class, 'store'])
                    ->name('store');

                Route::get('/{rol}/editar', [RolController::class, 'edit'])
                    ->name('edit');

                Route::put('/{rol}', [RolController::class, 'update'])
                    ->name('update');

                Route::delete('/{rol}', [RolController::class, 'destroy'])
                    ->name('destroy');
            });
    });

// Usuario normal (Protegido)
Route::middleware(['auth', 'force.password.change', 'rol:usuario', 'user.status'])->group(function () {
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

        Route::get('/datos-personales', fn () => view('auth.datos-personales'))
            ->name('datos.personales');

        Route::get('/omitir-perfil', function () {
            return redirect()
                ->route('user.dashboard')
                ->with(
                    'success',
                    'Has omitido el registro. Puedes completar tus datos más tarde desde tu perfil.'
                );
        })->name('perfil.omitir');

        Route::post('/datos-personales', [GoogleController::class, 'guardarDatosPersonales'])
            ->name('perfil.guardar');
    });

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::get('/vincular-cuenta', [GoogleController::class, 'showLinkAccountForm'])
    ->name('vincular.cuenta');

Route::post('/vincular-cuenta', [GoogleController::class, 'linkAccount'])
    ->name('vincular.cuenta.procesar');


// Google desde el perfil (Autenticado)
Route::get('/profile/google/link', [GoogleController::class, 'redirectToGoogleFromProfile'])->middleware(['auth', 'force.password.change'])->name('profile.google.link');
Route::delete('/profile/google', [ProfileController::class, 'unlinkGoogle'])->middleware(['auth', 'force.password.change'])->name('profile.google.unlink');
