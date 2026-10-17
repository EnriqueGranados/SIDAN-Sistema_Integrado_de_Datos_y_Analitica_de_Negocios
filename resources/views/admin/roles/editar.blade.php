@extends('layouts.navbars')

@section('title', 'Editar rol')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Editar rol: {{ ucfirst($rol->nombre) }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Los cambios de acceso se aplicarán a todos los usuarios que tengan este rol.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.roles.update', $rol) }}"
            class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900">
            @csrf
            @method('PUT')
            @include('admin.roles._formulario')
        </form>
    </div>
@endsection