@extends('layouts.navbars')

@section('title', 'Crear Nuevo Rol')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Crear Nuevo Rol</h1>
            <p class="text-sm text-gray-400 mt-1">Define un nuevo perfil de acceso para el sistema.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.roles.store') }}" method="POST" class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nombre del Rol *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: supervisor, editor" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
                <p class="text-xs text-gray-500 mt-1">Se guardará en minúsculas automáticamente.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Descripción *</label>
                <textarea name="descripcion" rows="4" placeholder="Describe los permisos y alcance de este rol..." class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>{{ old('descripcion') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-white/5">
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition">Guardar Rol</button>
            </div>
        </form>
    </div>
@endsection