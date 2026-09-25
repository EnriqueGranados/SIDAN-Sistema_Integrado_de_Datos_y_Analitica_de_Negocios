@extends('layouts.navbars')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Editar Usuario</h1>
        <p class="text-sm text-gray-400 mt-1">Actualiza la información del usuario seleccionado.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-[#0f172a] border border-white/5 rounded-2xl p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nombres *</label>
                <input type="text" name="nombres" value="{{ old('nombres', $user->informacion_personal->nombres) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Apellidos *</label>
                <input type="text" name="apellidos" value="{{ old('apellidos', $user->informacion_personal->apellidos) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Correo Electrónico *</label>
                <input type="email" name="correo" value="{{ old('correo', $user->correo) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Documento de Identidad *</label>
                <input type="text" name="documento" value="{{ old('documento', $user->informacion_personal->documento) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $user->informacion_personal->telefono) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->informacion_personal->fecha_nacimiento) }}" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Género</label>
                <select name="genero" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
                    <option value="">Seleccione</option>
                    <option value="M" {{ old('genero', $user->informacion_personal->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('genero', $user->informacion_personal->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="O" {{ old('genero', $user->informacion_personal->genero) == 'O' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Rol del Usuario *</label>
            <select name="id_rol" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition" required>
                <option value="">Seleccione un rol</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id_rol }}" {{ old('id_rol', $user->id_rol) == $rol->id_rol ? 'selected' : '' }}>
                        {{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nueva Contraseña <span class="text-gray-500 text-xs">(Dejar en blanco para mantener la actual)</span></label>
                <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-white/5">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Cancelar</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition">Actualizar Usuario</button>
        </div>
    </form>
</div>
@endsection