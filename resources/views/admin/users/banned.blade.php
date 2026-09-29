@extends('layouts.navbars')

@section('title', 'Usuarios Bloqueados')

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
                'restore_url' => route('admin.users.restore', $user),
            ];
        });
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Usuarios Bloqueados</h1>
                <p class="text-sm text-gray-400 mt-1">
                    Usuarios desactivados temporalmente (pueden ser restaurados)
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Listado
            </a>
        </div>

        <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden"
            x-data="bannedTable(@js($usersData), '{{ csrf_token() }}')">

            <div class="overflow-x-auto">
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
                        <template x-for="user in users" :key="user.id">
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                                            <span x-text="user.nombres.charAt(0).toUpperCase()"></span>
                                        </div>

                                        <div>
                                            <p class="text-white font-medium"
                                                x-text="user.nombres + ' ' + user.apellidos"></p>
                                            <p class="text-xs text-gray-500" x-text="user.documento"></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-300" x-text="user.correo"></td>

                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-gray-500/10 text-gray-400 border border-gray-500/20"
                                        x-text="user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)">
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <button @click="restoreUser(user)"
                                        class="px-3 py-1.5 text-sm text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition flex items-center gap-2 border border-emerald-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                            </path>
                                        </svg>
                                        Reactivar
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="users.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">No hay usuarios bloqueados</p>
                                <p class="text-sm mt-1">Todos los usuarios están activos en el sistema.</p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="${iconPath}"></path>
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
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
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

        function bannedTable(usersData, csrfToken) {
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
                                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
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
                                    class="confirm-btn px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
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

                async restoreUser(user) {
                    const confirmado = await this.showConfirm(
                        '¿Reactivar usuario?',
                        `Estás a punto de <strong>reactivar</strong> a <strong>${user.nombres} ${user.apellidos}</strong>.<br><br>
                        El usuario volverá al listado principal con estado activo y podrá iniciar sesión.`
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
                                    data.message || 'Usuario reactivado correctamente'
                                );
                            } else {
                                this.showNotification(
                                    'error',
                                    data.message || 'Error al reactivar el usuario'
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
@endsection