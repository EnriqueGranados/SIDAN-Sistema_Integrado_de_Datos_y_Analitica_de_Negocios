@extends('layouts.navbars')

@section('title', 'Usuarios Bloqueados')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Usuarios Bloqueados</h1>
                <p class="text-sm text-gray-400 mt-1">Usuarios desactivados temporalmente (pueden ser restaurados)</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                ← Volver al Listado
            </a>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/[0.02]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs text-gray-400 uppercase">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs text-gray-400 uppercase">Correo</th>
                        <th class="px-6 py-4 text-left text-xs text-gray-400 uppercase">Rol</th>
                        <th class="px-6 py-4 text-left text-xs text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/[0.02]">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->informacion_personal->nombres, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-medium">{{ $user->informacion_personal->nombres }} {{ $user->informacion_personal->apellidos }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->informacion_personal->documento }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $user->correo }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-500/10 text-gray-400">
                                    {{ ucfirst($user->rol->nombre) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.users.restore', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 text-sm text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition">
                                        ↻ Reactivar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No hay usuarios bloqueados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection