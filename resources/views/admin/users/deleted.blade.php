@extends('layouts.navbars')

@section('title', 'Usuarios Eliminados')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Usuarios Eliminados</h1>
                <p class="text-sm text-gray-400 mt-1">Usuarios eliminados permanentemente del sistema (solo visible para Superadmin)</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.banned') }}" class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                    Ver Baneados
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                    ← Volver al Listado
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

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
                        <tr class="hover:bg-white/[0.02] opacity-60">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->informacion_personal->nombres ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-white font-medium line-through decoration-red-500/50">
                                            {{ $user->informacion_personal->nombres ?? '' }} {{ $user->informacion_personal->apellidos ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $user->informacion_personal->documento ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">{{ $user->correo }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-500/10 text-gray-400">
                                    {{ ucfirst($user->rol->nombre) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.users.restore', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 text-sm text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition flex items-center gap-2" onclick="return confirm('¿Restaurar este usuario eliminado? Volverá al listado principal como activo.')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Restaurar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-lg font-medium">No hay usuarios eliminados</p>
                                <p class="text-sm mt-1">Todos los usuarios están activos o baneados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-white/5">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection