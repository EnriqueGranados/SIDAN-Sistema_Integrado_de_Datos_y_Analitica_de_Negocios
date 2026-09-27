@extends('layouts.navbars')

@section('title', 'Gestión de Roles')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Gestión de Roles</h1>
                <p class="text-sm text-gray-400 mt-1">Administra los perfiles y permisos del sistema</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear Nuevo Rol
            </a>
        </div>

        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/[0.02] border-b border-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nombre del Rol</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Usuarios Asignados</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($roles as $rol)
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full border 
                                    @if($rol->nombre === 'superadmin') bg-purple-500/10 text-purple-400 border-purple-500/20
                                    @elseif($rol->nombre === 'admin') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @else bg-gray-500/10 text-gray-400 border-gray-500/20 @endif">
                                    {{ ucfirst($rol->nombre) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $rol->descripcion }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    {{ $rol->usuarios_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.roles.edit', $rol) }}" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    @if(!in_array($rol->nombre, ['superadmin', 'admin', 'usuario']))
                                        <form action="{{ route('admin.roles.destroy', $rol) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este rol? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-lg font-medium">No hay roles personalizados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($roles->hasPages())
                <div class="px-6 py-4 border-t border-white/5">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>
@endsection