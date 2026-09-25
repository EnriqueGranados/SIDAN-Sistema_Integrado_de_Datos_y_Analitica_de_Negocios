<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Ruta principal.
Route::get('/', function () {
    if (!auth()->check()) {
        return view('welcome');
    }

    $rol = auth()->user()->rol->nombre;

    if (in_array($rol, ['superadmin', 'admin'])) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('user.dashboard');
});

// Rutas de autenticación de Breeze (login, register, logout)
require __DIR__.'/auth.php';

// Rutas de Perfil (Para que el menú de navegación no falle)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas protegidas para administradores.
Route::middleware(['auth', 'rol:superadmin,admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Rutas protegidas solo para el usuario normal.
Route::middleware(['auth', 'rol:usuario'])->group(function () {
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');
});