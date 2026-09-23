<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
    <div class="p-6 lg:p-8">

        
        {{-- VISTA PARA INVITADOS (LOGIN / REGISTRO) --}}
        <h1 class="text-2xl font-bold mb-4">Bienvenido a SIDAN</h1>
        <p class="mb-6 text-gray-600 dark:text-gray-400">Inicia sesión o crea una cuenta para continuar.</p>
        <div class="flex gap-4">
            <a href="{{ route('login') }}" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
                Iniciar Sesión
            </a>
            <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Crear Nueva Cuenta
            </a>
        </div>

    </div>
</body>
</html>