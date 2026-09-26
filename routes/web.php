<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/actividades/{slug}', [WelcomeController::class, 'show'])
    ->name('activities.show');

// Rutas de autenticación
require __DIR__.'/auth.php';

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Administradores
Route::middleware(['auth', 'rol:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Ruta para búsqueda en tiempo real (devuelve JSON)
    Route::get('/users/search', [\App\Http\Controllers\Admin\UserController::class, 'search'])
        ->name('users.search');

    // CRUD de Usuarios (Ahora sí generará admin.users.index, admin.users.create, etc.)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Ruta para cambiar el estado (activo/inactivo)
    Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggleEstado'])
        ->name('users.toggle');

});

// Usuario normal
Route::middleware(['auth', 'rol:usuario'])->group(function () {
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
});