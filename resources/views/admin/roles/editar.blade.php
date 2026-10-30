@extends('layouts.navbars')

@section('title', 'Editar rol')

@section('content')
<div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Administración
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                    Editar rol: {{ ucfirst($rol->nombre) }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Los cambios de acceso se aplicarán a todos los usuarios que tengan este rol.
                </p>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-colors dark:bg-[#0f172a] dark:border-white/5">
            <div class="overflow-x-auto">
                <form method="POST" action="{{ route('admin.roles.update', $rol) }}"
                    class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900">
                    @csrf
                    @method('PUT')
                    @include('admin.roles._formulario')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection