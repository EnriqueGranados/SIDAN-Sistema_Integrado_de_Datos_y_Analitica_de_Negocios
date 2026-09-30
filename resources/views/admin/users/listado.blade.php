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
                <h1 class="text-2xl font-black text-sidan-900 dark:text-white">
                    Listado de Usuarios
                </h1>

                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Administra los usuarios y sus permisos en el sistema
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.users.banned') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-sidan-900 dark:text-gray-300 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10 dark:hover:text-white transition flex items-center gap-2">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                        </path>
                    </svg>

                    Ver Bloqueados
                </a>

                @if (auth()->user()->rol->nombre === 'superadmin')
                    <a href="{{ route('admin.users.deleted') }}"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-xl hover:bg-red-50 dark:text-gray-300 dark:bg-white/5 dark:border-red-500/20 dark:hover:bg-red-500/10 dark:hover:text-red-400 transition flex items-center gap-2">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>

                        Ver Eliminados
                    </a>
                @endif

                <a href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 text-sm font-bold text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                        </path>
                    </svg>

                    Crear Nuevo Usuario
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm transition-colors dark:bg-[#0f172a] dark:border-white/5"
            x-data="{
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

                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 dark:text-gray-500"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                    </path>
                </svg>

                <input type="text" x-model="search" @input.debounce.300ms="performSearch()"
                    placeholder="Buscar por nombre, apellido, documento o correo..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition dark:bg-white/5 dark:border-white/10 dark:text-white dark:placeholder-gray-500 dark:focus:bg-white/10">

                <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">

                    <svg class="animate-spin h-5 w-5 text-emerald-500 dark:text-emerald-400"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">

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

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-colors dark:bg-[#0f172a] dark:border-white/5"
            x-data="userTable(@js($usersData), '{{ csrf_token() }}')" @users-updated.window="users = $event.detail">

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-slate-50 border-b border-slate-200 dark:bg-white/[0.02] dark:border-white/5">

                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-gray-400">
                                Usuario
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-gray-400">
                                Correo
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-gray-400">
                                Rol
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-gray-400">
                                Estado
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider dark:text-gray-400">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-white/5">

                        <template x-for="user in users" :key="user.id">

                            <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition"
                                :class="{ 'opacity-50': !user.estado_activo }">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm"
                                            x-text="user.nombres.charAt(0).toUpperCase()">
                                        </div>

                                        <div>
                                            <p class="text-sm font-semibold text-sidan-900 dark:text-white"
                                                x-text="user.nombres + ' ' + user.apellidos">
                                            </p>

                                            <p class="text-xs text-slate-500 dark:text-gray-500" x-text="user.documento">
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-gray-300" x-text="user.correo">
                                </td>

                                <td class="px-6 py-4">

                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full border"
                                        :class="{
                                            'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20': user
                                                .rol_nombre === 'superadmin',
                                            'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20': user
                                                .rol_nombre === 'admin',
                                            'bg-slate-100 text-slate-600 border-slate-200 dark:bg-gray-500/10 dark:text-gray-400 dark:border-gray-500/20': user
                                                .rol_nombre === 'usuario'
                                        }"
                                        x-text="user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)">
                                    </span>
                                </td>

                                <td class="px-6 py-4">

                                    <button @click="toggleStatus(user)"
                                        :class="user.estado_activo ?
                                            'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 dark:border-red-500/20' :
                                            'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 dark:border-emerald-500/20'"
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

                                        <span x-text="user.estado_activo ? 'Bloquear' : 'Reactivar'">
                                        </span>
                                    </button>
                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-2">

                                        <a :href="user.edit_url"
                                            class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-500/10 rounded-lg transition"
                                            title="Editar información del usuario">

                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        <button @click="banUser(user)"
                                            class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-500/10 rounded-lg transition"
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
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-gray-500">

                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>

                                <p class="text-lg font-semibold text-slate-700 dark:text-gray-300">
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

            const iconColor = isSuccess ?
                'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' :
                'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';

            const borderColor = isSuccess ?
                'border-emerald-200 dark:border-emerald-500/20' :
                'border-red-200 dark:border-red-500/20';

            const progressColor = isSuccess ?
                'bg-emerald-500' :
                'bg-red-500';

            const progressBackground = isSuccess ?
                'bg-emerald-100 dark:bg-emerald-950/50' :
                'bg-red-100 dark:bg-red-950/50';

            const title = isSuccess ?
                'Acción completada' :
                'Ocurrió un error';

            const iconPath = isSuccess ?
                'M5 13l4 4L19 7' :
                'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z';

            notification.innerHTML = `
                <div class="relative overflow-hidden rounded-2xl border ${borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
                    <div class="flex items-start gap-3 p-4 pr-12">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${iconColor}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="${iconPath}">
                                </path>
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-sidan-900 dark:text-white">
                                ${title}
                            </p>

                            <p
                                class="notification-message mt-1 text-sm leading-5 text-slate-600 dark:text-gray-400">
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                        aria-label="Cerrar alerta">

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>

                    <div class="h-1 w-full ${progressBackground}">
                        <div
                            class="notification-progress h-full w-full origin-left ${progressColor}">
                        </div>
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

                notification.classList.remove(
                    'opacity-100',
                    'translate-y-0'
                );

                notification.classList.add(
                    'opacity-0',
                    'translate-y-6'
                );

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            };

            closeButton.addEventListener(
                'click',
                closeNotification
            );

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    notification.classList.remove(
                        'opacity-0',
                        'translate-y-6'
                    );

                    notification.classList.add(
                        'opacity-100',
                        'translate-y-0'
                    );

                    progress.style.transition = 'transform 5s linear';
                    progress.style.transform = 'scaleX(0)';
                });
            });

            closeTimeout = setTimeout(
                closeNotification,
                5000
            );
        }

        function userTable(usersData, csrfToken) {
            return {
                users: usersData,

                showConfirm(title, message) {
                    if (document.getElementById('app-confirm-overlay')) {
                        return Promise.resolve(false);
                    }

                    return new Promise((resolve) => {
                        const overlay = document.createElement('div');

                        overlay.id = 'app-confirm-overlay';
                        overlay.className =
                            'fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300';

                        const modal = document.createElement('div');

                        modal.setAttribute('role', 'dialog');
                        modal.setAttribute('aria-modal', 'true');
                        modal.setAttribute('tabindex', '-1');

                        modal.className =
                            'bg-white border border-slate-200 dark:bg-[#0f172a] dark:border-white/10 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300';

                        modal.innerHTML = `
                            <div class="flex items-center gap-3 mb-4">

                                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">

                                    <svg
                                        class="w-6 h-6 text-emerald-600 dark:text-emerald-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-xl font-bold text-sidan-900 dark:text-white">
                                        ${title}
                                    </h3>

                                    <p class="text-xs text-slate-500 dark:text-gray-500 mt-1">
                                        Confirma antes de continuar
                                    </p>
                                </div>
                            </div>

                            <div class="text-slate-600 dark:text-gray-300 mb-6 leading-relaxed text-sm">
                                ${message}
                            </div>

                            <div class="bg-emerald-50 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20 rounded-lg p-3 mb-6">
                                <p class="text-sm text-emerald-700 dark:text-emerald-300">
                                    El usuario recuperará el acceso al sistema inmediatamente.
                                </p>
                            </div>

                            <div class="flex gap-3 justify-end">

                                <button
                                    type="button"
                                    class="cancel-btn px-4 py-2 text-sm font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 dark:text-gray-300 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10 transition">

                                    Cancelar
                                </button>

                                <button
                                    type="button"
                                    class="confirm-btn px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 transition flex items-center gap-2">

                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7">
                                        </path>
                                    </svg>

                                    Confirmar
                                </button>
                            </div>
                        `;

                        overlay.appendChild(modal);
                        document.body.appendChild(overlay);

                        const confirmButton = modal.querySelector('.confirm-btn');
                        const cancelButton = modal.querySelector('.cancel-btn');

                        let resolved = false;

                        const close = (value) => {
                            if (resolved) {
                                return;
                            }

                            resolved = true;

                            confirmButton.disabled = true;
                            cancelButton.disabled = true;

                            document.removeEventListener('keydown', handleKeydown, true);

                            overlay.classList.remove('opacity-100');
                            overlay.classList.add('opacity-0');

                            modal.classList.remove(
                                'scale-100',
                                'opacity-100'
                            );

                            modal.classList.add(
                                'scale-95',
                                'opacity-0'
                            );

                            setTimeout(() => {
                                overlay.remove();
                                resolve(value);
                            }, 300);
                        };

                        const handleKeydown = (event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                event.stopPropagation();

                                if (event.repeat || resolved) {
                                    return;
                                }

                                close(true);
                            }

                            if (event.key === 'Escape') {
                                event.preventDefault();
                                event.stopPropagation();

                                if (resolved) {
                                    return;
                                }

                                close(false);
                            }
                        };

                        confirmButton.addEventListener('click', () => {
                            close(true);
                        });

                        cancelButton.addEventListener('click', () => {
                            close(false);
                        });

                        overlay.addEventListener('click', (event) => {
                            if (event.target === overlay) {
                                close(false);
                            }
                        });

                        document.addEventListener(
                            'keydown',
                            handleKeydown,
                            true
                        );

                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                overlay.classList.remove('opacity-0');
                                overlay.classList.add('opacity-100');

                                modal.classList.remove(
                                    'scale-95',
                                    'opacity-0'
                                );

                                modal.classList.add(
                                    'scale-100',
                                    'opacity-100'
                                );

                                modal.focus();
                            });
                        });
                    });
                },
                
                async toggleStatus(user) {
                    const willBeBanned = user.estado_activo;

                    const titulo = willBeBanned ?
                        '¿Bloquear usuario?' :
                        '¿Reactivar usuario?';

                    const mensaje = willBeBanned ?
                        `
                            Estás a punto de <strong>bloquear</strong> a <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>
                            El usuario será movido a la lista de "Bloqueados" y no podrá iniciar sesión.<br>
                            Esta acción puede ser revertida manualmente.
                        ` :
                        `
                            Estás a punto de <strong>reactivar</strong> a <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>
                            El usuario volverá al listado principal y podrá iniciar sesión.
                        `;

                    const confirmado = await this.showConfirm(
                        titulo,
                        mensaje
                    );

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
                            this.showNotification(
                                'error',
                                'Error de conexión'
                            );
                        });
                },

                async banUser(user) {
                    const titulo = '¿Eliminar permanentemente?';

                    const mensaje = `
                        Estás a punto de <strong>eliminar</strong> a <strong>${user.nombres} ${user.apellidos}</strong> del sistema.<br><br>

                        <div class="bg-red-50 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 rounded-lg p-3 my-3">

                            <p class="text-sm text-red-600 dark:text-red-300">
                                <strong>Esta acción:</strong>
                            </p>

                            <ul class="text-sm text-red-600 dark:text-red-300 mt-2 space-y-1 list-disc list-inside">
                                <li>Eliminará al usuario de TODAS las vistas</li>
                                <li>Solo será visible en "Usuarios Eliminados"</li>
                                <li>El usuario no podrá iniciar sesión</li>
                            </ul>
                        </div>

                        <p class="text-sm text-amber-600 dark:text-yellow-400">
                            Esta acción puede ser revertida manualmente desde la sección de Eliminados.
                        </p>
                    `;

                    const confirmado = await this.showConfirm(
                        titulo,
                        mensaje
                    );

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
                            this.showNotification(
                                'error',
                                'Error de conexión'
                            );
                        });
                },

                showNotification(type, message) {
                    showAppNotification(type, message);
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showAppNotification(
                    'success',
                    @json(session('success'))
                );
            @endif

            @if (session('error'))
                showAppNotification(
                    'error',
                    @json(session('error'))
                );
            @endif
        });
    </script>
@endsection
