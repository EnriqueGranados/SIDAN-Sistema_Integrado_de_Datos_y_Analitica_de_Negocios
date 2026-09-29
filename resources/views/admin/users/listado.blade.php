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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Listado de Usuarios</h1>
                <p class="text-sm text-gray-400 mt-1">Administra los usuarios y sus permisos en el sistema</p>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4"></path>
                    </svg>
                    Crear Nuevo Usuario
                </a>
            </div>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl p-4" x-data="{
            search: '',
            loading: false,
            performSearch() {
                this.loading = true;

                fetch(`{{ route('admin.users.search') }}?q=${encodeURIComponent(this.search)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.$dispatch('users-updated', data.users);
                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                        showAppNotification('error', 'Error al realizar la búsqueda');
                    });
            }
        }">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <input type="text" x-model="search" @input.debounce.300ms="performSearch()"
                    placeholder="Buscar por nombre, apellido, documento o correo..."
                    class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/50 focus:bg-white/10 transition">

                <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden"
            x-data="userTable(@js($usersData), '{{ csrf_token() }}')"
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
                                Estado
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/5">
                        <template x-for="user in users" :key="user.id">
                            <tr class="hover:bg-white/[0.02] transition"
                                :class="{ 'opacity-50': !user.estado_activo }">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm"
                                            x-text="user.nombres.charAt(0).toUpperCase()">
                                        </div>

                                        <div>
                                            <p class="text-sm font-medium text-white"
                                                x-text="user.nombres + ' ' + user.apellidos"></p>
                                            <p class="text-xs text-gray-500" x-text="user.documento"></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-300" x-text="user.correo"></td>

                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full border"
                                        :class="{
                                            'bg-purple-500/10 text-purple-400 border-purple-500/20': user.rol_nombre === 'superadmin',
                                            'bg-blue-500/10 text-blue-400 border-blue-500/20': user.rol_nombre === 'admin',
                                            'bg-gray-500/10 text-gray-400 border-gray-500/20': user.rol_nombre === 'usuario'
                                        }"
                                        x-text="user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)">
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <button @click="toggleStatus(user)"
                                        :class="user.estado_activo
                                            ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20'
                                            : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-2">

                                        <svg x-show="user.estado_activo" class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                            </path>
                                        </svg>

                                        <svg x-show="!user.estado_activo" class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z">
                                            </path>
                                        </svg>

                                        <span x-text="user.estado_activo ? 'Bloquear' : 'Reactivar'"></span>
                                    </button>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a :href="user.edit_url"
                                            class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition"
                                            title="Editar información del usuario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        <button @click="banUser(user)"
                                            class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition"
                                            title="Eliminar permanentemente (Borrado lógico)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="users.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>

                                <p class="text-lg font-medium">No se encontraron usuarios</p>
                                <p class="text-sm mt-1">Intenta con otra búsqueda o crea un nuevo usuario</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<script>
    function showAppNotification(type, message) {
        const previousNotification = document.getElementById('app-notification');

        if (previousNotification) {
            previousNotification.remove();
        }

        const isSuccess = type === 'success';
        const notification = document.createElement('div');

        notification.id = 'app-notification';
        notification.className =
            'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';

        const iconColor = isSuccess
            ? 'bg-emerald-500/10 text-emerald-400'
            : 'bg-red-500/10 text-red-400';

        const borderColor = isSuccess
            ? 'border-emerald-500/20'
            : 'border-red-500/20';

        const progressColor = isSuccess
            ? 'bg-emerald-500'
            : 'bg-red-500';

        const progressBackground = isSuccess
            ? 'bg-emerald-950/50'
            : 'bg-red-950/50';

        const title = isSuccess
            ? 'Acción completada'
            : 'Ocurrió un error';

        const iconPath = isSuccess
            ? 'M5 13l4 4L19 7'
            : 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z';

        notification.innerHTML = `
            <div class="relative overflow-hidden rounded-2xl border ${borderColor} bg-[#0f172a] shadow-2xl">
                <div class="flex items-start gap-3 p-4 pr-12">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${iconColor}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-black text-white">${title}</p>
                        <p class="notification-message mt-1 text-sm leading-5 text-gray-400"></p>
                    </div>
                </div>

                <button type="button"
                    class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Cerrar alerta">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="h-1 w-full ${progressBackground}">
                    <div class="notification-progress h-full w-full origin-left ${progressColor}"></div>
                </div>
            </div>
        `;

        notification.querySelector('.notification-message').textContent = message;
        document.body.appendChild(notification);

        const progress = notification.querySelector('.notification-progress');
        const closeButton = notification.querySelector('.notification-close');
        let closeTimeout;

        const closeNotification = () => {
            clearTimeout(closeTimeout);

            notification.classList.remove('opacity-100', 'translate-y-0');
            notification.classList.add('opacity-0', 'translate-y-6');

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        };

        closeButton.addEventListener('click', closeNotification);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                notification.classList.remove('opacity-0', 'translate-y-6');
                notification.classList.add('opacity-100', 'translate-y-0');

                progress.style.transition = 'transform 5s linear';
                progress.style.transform = 'scaleX(0)';
            });
        });

        closeTimeout = setTimeout(closeNotification, 5000);
    }

    function userTable(usersData, csrfToken) {
        return {
            users: usersData,

            showConfirm(title, message) {
                return new Promise((resolve) => {
                    const overlay = document.createElement('div');

                    overlay.className =
                        'fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity duration-300';

                    const modal = document.createElement('div');

                    modal.className =
                        'bg-[#0f172a] border border-white/10 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300';

                    modal.innerHTML = `
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white">${title}</h3>
                        </div>

                        <div class="text-gray-300 mb-6 leading-relaxed text-sm">${message}</div>

                        <div class="flex gap-3 justify-end">
                            <button
                                class="cancel-btn px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                                Cancelar
                            </button>

                            <button
                                class="confirm-btn px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Confirmar
                            </button>
                        </div>
                    `;

                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);

                    requestAnimationFrame(() => {
                        modal.classList.remove('scale-95', 'opacity-0');
                        modal.classList.add('scale-100', 'opacity-100');
                    });

                    const cleanup = () => {
                        modal.classList.remove('scale-100', 'opacity-100');
                        modal.classList.add('scale-95', 'opacity-0');
                        overlay.classList.add('opacity-0');

                        setTimeout(() => {
                            overlay.remove();
                        }, 300);
                    };

                    modal.querySelector('.confirm-btn').addEventListener('click', () => {
                        cleanup();
                        resolve(true);
                    });

                    modal.querySelector('.cancel-btn').addEventListener('click', () => {
                        cleanup();
                        resolve(false);
                    });

                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            cleanup();
                            resolve(false);
                        }
                    });
                });
            },

            async toggleStatus(user) {
                const willBeBanned = user.estado_activo;

                const titulo = willBeBanned
                    ? '¿Bloquear usuario?'
                    : '¿Reactivar usuario?';

                const mensaje = willBeBanned
                    ? `
                        Estás a punto de <strong>bloquear</strong> a <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>
                        El usuario será movido a la lista de "Bloqueados" y no podrá iniciar sesión.<br>
                        Esta acción puede ser revertida manualmente.
                    `
                    : `
                        Estás a punto de <strong>reactivar</strong> a <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>
                        El usuario volverá al listado principal y podrá iniciar sesión.
                    `;

                const confirmado = await this.showConfirm(titulo, mensaje);

                if (!confirmado) {
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
                            user.estado_activo = !willBeBanned;

                            if (willBeBanned) {
                                this.users = this.users.filter(
                                    u => String(u.id) !== String(user.id)
                                );
                            }

                            this.showNotification(
                                'success',
                                data.message || 'Estado actualizado correctamente'
                            );
                        } else {
                            this.showNotification(
                                'error',
                                data.message || 'Error al actualizar el estado'
                            );
                        }
                    })
                    .catch(() => {
                        this.showNotification('error', 'Error de conexión');
                    });
            },

            async banUser(user) {
                const titulo = '¿Eliminar permanentemente?';

                const mensaje = `
                    Estás a punto de <strong>eliminar</strong> a <strong>${user.nombres} ${user.apellidos}</strong> del sistema.<br><br>

                    <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 my-3">
                        <p class="text-sm text-red-300"><strong>Esta acción:</strong></p>

                        <ul class="text-sm text-red-300 mt-2 space-y-1 list-disc list-inside">
                            <li>Eliminará al usuario de TODAS las vistas</li>
                            <li>Solo será visible en "Usuarios Eliminados"</li>
                            <li>El usuario no podrá iniciar sesión</li>
                        </ul>
                    </div>

                    <p class="text-sm text-yellow-400">
                        Esta acción puede ser revertida manualmente desde la sección de Eliminados.
                    </p>
                `;

                const confirmado = await this.showConfirm(titulo, mensaje);

                if (!confirmado) {
                    return;
                }

                const confirmacionFinal = await this.showConfirm(
                    'CONFIRMACIÓN FINAL',
                    `
                        ¿Estás <strong>completamente seguro</strong> de eliminar a
                        <strong>${user.nombres} ${user.apellidos}</strong>?<br><br>
                        Esta es una acción crítica que requiere confirmación adicional.
                    `
                );

                if (!confirmacionFinal) {
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
                            this.users = this.users.filter(
                                u => String(u.id) !== String(user.id)
                            );

                            this.showNotification(
                                'success',
                                data.message || 'Usuario eliminado correctamente'
                            );
                        } else {
                            this.showNotification(
                                'error',
                                data.message || 'Error al eliminar el usuario'
                            );
                        }
                    })
                    .catch(() => {
                        this.showNotification('error', 'Error de conexión');
                    });
            },

            showNotification(type, message) {
                showAppNotification(type, message);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            showAppNotification('success', @json(session('success')));
        @endif

        @if (session('error'))
            showAppNotification('error', @json(session('error')));
        @endif
    });
</script>