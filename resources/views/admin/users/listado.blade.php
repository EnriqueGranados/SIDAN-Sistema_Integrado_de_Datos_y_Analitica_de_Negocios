@extends('layouts.navbars')

@section('title', 'Gestión de Usuarios')

@section('content')

    @php
        $usersData = $users->map(function ($user) {
            return [
                'id' => $user->id_usuario,
                'nombres' => $user->informacion_personal->nombres ?? '',
                'apellidos' => $user->informacion_personal->apellidos ?? '',
                'documento' => $user->informacion_personal->documento ?? '',
                'correo' => $user->correo,
                'rol_nombre' => $user->rol->nombre,
                'estado_activo' => $user->estado_activo,
                'edit_url' => route('admin.users.edit', $user),
                'toggle_url' => route('admin.users.toggle', $user),
                'ban_url' => route('admin.users.destroy', $user),
            ];
        });
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Listado de Usuarios
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Administra los usuarios y sus permisos en el sistema
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.users.banned') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition flex items-center gap-2">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                        </path>
                    </svg>

                    Ver Bloqueados

                </a>

                {{-- Botón solo visible para Superadmin --}}
                @if (auth()->user()->rol->nombre === 'superadmin')
                    <a href="{{ route('admin.users.deleted') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-red-500/20 rounded-lg hover:bg-red-500/10 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Ver Eliminados
                    </a>
                @endif

                <a href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                        </path>

                    </svg>

                    Crear Nuevo Usuario

                </a>
            </div>

        </div>


        {{-- BUSCADOR --}}
        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-4" x-data="{
            search: '',
            loading: false,
            performSearch() {
                this.loading = true;
                fetch(`{{ route('admin.users.search') }}?q=${this.search}`)
                    .then(response => response.json())
                    .then(data => {
                        this.$dispatch('users-updated', data.users);
                        this.loading = false;
                    });
            }
        }">

            <div class="relative">

                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                    </path>

                </svg>

                <input type="text" x-model="search" @input.debounce.300ms="performSearch()"
                    placeholder="Buscar por nombre, apellido, documento o correo..."
                    class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">

                <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">

                    <svg class="animate-spin h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">

                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4">
                        </circle>

                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>

                    </svg>

                </div>

            </div>

        </div>


        {{-- TABLA DE USUARIOS --}}
        <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden" x-data="userTable(@js($usersData), '{{ csrf_token() }}')"
            @users-updated.window="users = $event.detail">

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-white/[0.02] border-b border-white/5">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Usuario
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Correo
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Rol
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Bloquear
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <template x-for="user in users" :key="user.id">
                            <tr class="hover:bg-white/[0.02] transition" :class="{ 'opacity-50': !user.estado_activo }">
                                {{-- USUARIO --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm"
                                            x-text="user.nombres.charAt(0).toUpperCase()">
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-white"
                                                x-text="user.nombres + ' ' + user.apellidos">
                                            </p>
                                            <p class="text-xs text-gray-500" x-text="user.documento">
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                {{-- CORREO --}}
                                <td class="px-6 py-4 text-sm text-gray-300" x-text="user.correo">
                                </td>
                                {{-- ROL --}}
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full border"
                                        :class="{
                                            'bg-purple-500/10 text-purple-400 border-purple-500/20': user
                                                .rol_nombre === 'superadmin',
                                            'bg-blue-500/10 text-blue-400 border-blue-500/20': user
                                                .rol_nombre === 'admin',
                                            'bg-gray-500/10 text-gray-400 border-gray-500/20': user
                                                .rol_nombre === 'usuario'
                                        }"
                                        x-text="user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)">
                                    </span>
                                </td>
                                {{-- ESTADO --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" :checked="user.estado_activo"
                                                @change="toggleStatus(user)">
                                            <div
                                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                            </div>
                                        </label>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a :href="user.edit_url"
                                            class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition"
                                            title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        <button @click="banUser(user)"
                                            class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition"
                                            title="Eliminar permanentemente">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        {{-- SIN RESULTADOS --}}
                        <tr x-show="users.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">
                                    No se encontraron usuarios
                                </p>
                                <p class="text-sm mt-1">
                                    Intenta con otra búsqueda o crea un nuevo usuario
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

{{-- Script separado para Alpine.js --}}
<script>
    function userTable(usersData, csrfToken) {
        return {
            users: usersData,

            toggleStatus(user) {
                const accion = user.estado_activo ? 'banear' : 'reactivar';
                if (!confirm(`¿Estás seguro de ${accion} esta cuenta?`)) {
                    return;
                }

                fetch(user.toggle_url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Si se baneó, remover de la lista
                            if (!user.estado_activo) {
                                this.users = this.users.filter(u => u.id !== user.id);
                            } else {
                                user.estado_activo = !user.estado_activo;
                            }
                            this.showNotification('success', data.message || 'Estado actualizado correctamente');
                        } else {
                            this.showNotification('error', data.message || 'Error al actualizar el estado');
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Error de conexión');
                    });
            },

            banUser(user) {
                if (!confirm(
                        `️ ADVERTENCIA: ¿Estás seguro de BLOQUEAR esta cuenta?\n\nUsuario: ${user.nombres} ${user.apellidos}\n\nEsta acción:\n- Desactivará la cuenta inmediatamente\n- El usuario no podrá iniciar sesión\n- Los datos se mantendrán en la base de datos\n\nEsta acción puede ser revertida manualmente.`
                    )) {
                    return;
                }

                if (!confirm('¿CONFIRMAR BLOQUEO? Esta es tu última oportunidad para cancelar.')) {
                    return;
                }

                fetch(user.ban_url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.users = this.users.filter(u => u.id !== user.id);
                            this.showNotification('success', data.message || 'Usuario bloqueado correctamente');
                        } else {
                            this.showNotification('error', data.message || 'Error al bloquear el usuario');
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Error de conexión');
                    });
            },

            showNotification(type, message) {
                const div = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-emerald-500 text-white' :
                    'bg-red-500/10 border border-red-500/20 text-red-400';
                const iconPath = type === 'success' ? 'M5 13l4 4L19 7' :
                    'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';

                div.className =
                    `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 transition-opacity duration-500 ${bgColor}`;
                div.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                    </svg>
                    <span>${message}</span>
                `;

                document.body.appendChild(div);

                setTimeout(() => {
                    div.style.opacity = '0';
                    setTimeout(() => div.remove(), 500);
                }, 3000);
            }
        }
    }
</script>
