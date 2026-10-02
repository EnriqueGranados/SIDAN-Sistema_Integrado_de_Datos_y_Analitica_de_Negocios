@extends('layouts.navbars')

@section('title', 'Usuarios Eliminados')

@section('content')
    @php
        $usersData = $users->map(function ($user) {
            return [
                'id' => $user->id_usuario,
                'imagen_perfil' => $user->imagen_perfil,
                'nombres' => $user->informacion_personal->nombres ?? '',
                'apellidos' => $user->informacion_personal->apellidos ?? '',
                'documento' => $user->informacion_personal->documento ?? '',
                'correo' => $user->correo,
                'rol_nombre' => $user->rol->nombre,
                'restore_url' => route('admin.users.restore', $user),
            ];
        });
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-sidan-900 dark:text-white">
                    Usuarios Eliminados
                </h1>

                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Usuarios eliminados permanentemente del sistema (solo visible para Superadmin)
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-sidan-900 dark:text-gray-300 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10 dark:hover:text-white transition flex items-center gap-2">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18">
                        </path>
                    </svg>

                    Volver al Listado
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-colors dark:bg-[#0f172a] dark:border-white/5"
            x-data="deletedTable(@js($usersData), '{{ csrf_token() }}')">

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
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-white/5">

                        <template x-for="user in users" :key="user.id">

                            <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <img
                                            :src="user.imagen_perfil ? '{{ asset('storage') }}/' + user.imagen_perfil : '{{ asset('images/usuario.png') }}'"
                                            alt="Foto de perfil"
                                            class="h-10 w-10 shrink-0 rounded-full object-cover border border-slate-200 dark:border-white/10"
                                        >

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
                                                .rol_nombre === 'usuario',
                                            'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20':
                                                !['superadmin', 'admin', 'usuario'].includes(user.rol_nombre)
                                        }"
                                        x-text="user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)">
                                    </span>
                                </td>

                                <td class="px-6 py-4">

                                    <button @click="restoreUser(user)"
                                        class="px-3 py-1.5 text-sm font-medium text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition flex items-center gap-2 dark:text-emerald-400 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 dark:border-emerald-500/20">

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                            </path>
                                        </svg>

                                        Restaurar
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="users.length === 0">

                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-gray-500">

                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>

                                <p class="text-lg font-semibold text-slate-700 dark:text-gray-300">
                                    No hay usuarios eliminados
                                </p>

                                <p class="text-sm mt-1">
                                    Todos los usuarios están activos o bloqueados.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-white/5">
                    {{ $users->links() }}
                </div>
            @endif
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
                'Usuario restaurado' :
                'No se pudo restaurar el usuario';

            const iconPath = isSuccess ?
                'M5 13l4 4L19 7' :
                'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z';

            notification.innerHTML = `
                <div class="relative overflow-hidden rounded-2xl border ${borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">

                    <div class="flex items-start gap-3 p-4 pr-12">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${iconColor}">
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

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

        function deletedTable(usersData, csrfToken) {
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
                async restoreUser(user) {

                    const confirmado = await this.showConfirm(
                        '¿Restaurar usuario eliminado?',
                        `
                            Estás a punto de <strong>restaurar</strong> a
                            <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>

                            El usuario volverá al listado principal con estado activo y podrá iniciar sesión nuevamente.
                        `
                    );

                    if (!confirmado) {
                        return;
                    }

                    fetch(user.restore_url, {
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

                                this.users = this.users.filter(
                                    u => String(u.id) !== String(user.id)
                                );

                                this.showNotification(
                                    'success',
                                    data.message || 'Usuario restaurado correctamente'
                                );

                            } else {

                                this.showNotification(
                                    'error',
                                    data.message || 'No se pudo restaurar el usuario'
                                );
                            }
                        })
                        .catch(() => {

                            this.showNotification(
                                'error',
                                'No se pudo conectar con el servidor'
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
