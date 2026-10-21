@extends('layouts.navbars')

@section('title', 'Gestión de Usuarios')

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
                'estado_activo' => $user->estado_activo,
                'edit_url' => route('admin.users.edit', $user),
                'toggle_url' => route('admin.users.toggle', $user),
                'ban_url' => route('admin.users.destroy', $user),
            ];
        });
    @endphp

    @php
        $imagenesIniciales = $usersData->pluck('imagen_perfil', 'id')->all();
    @endphp

    {{-- Los botones de grupo son visuales; los permisos siguen controlados en Laravel. --}}
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white"
        x-data="{
            tipoUsuario: 'normales',
            imagenesIniciales: @js($imagenesIniciales),
            usuariosPorTipo(users) {
                return users.filter(user => {
                    const esNormal = String(user.rol_nombre || '').trim().toLowerCase() === 'usuario';
                    return this.tipoUsuario === 'normales' ? esNormal : !esNormal;
                });
            }
        }">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        Usuarios
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Administra los usuarios registrados, sus roles, permisos y acceso al sistema.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    <a href="{{ route('admin.users.banned') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>

                        Bloqueados
                    </a>

                    @if (auth()->user()->rol->nombre === 'superadmin')
                        <a href="{{ route('admin.users.deleted') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:border-red-500/20 dark:bg-white/5 dark:text-red-400 dark:hover:bg-red-500/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>

                            Eliminados
                        </a>
                    @endif

                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sidan-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>

                        Nuevo usuario
                    </a>

                </div>
            </div>

            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10"
                x-init="performSearch()"
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
                
                                showAppNotification(
                                    'error',
                                    'Error al realizar la búsqueda.'
                                );
                            });
                    }
                }">
                {{-- Filtro visible de cuentas: no modifica roles ni permisos. --}}
                <div class="mb-4">
                    <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Tipo de usuario
                    </p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2" role="group" aria-label="Filtrar usuarios por tipo">
                        <button type="button"
                            @click="tipoUsuario = 'normales'"
                            :aria-pressed="tipoUsuario === 'normales'"
                            :class="tipoUsuario === 'normales'
                                ? 'border-sidan-500 bg-sidan-500 text-white shadow-sm'
                                : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-sidan-300 hover:bg-sidan-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10'"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-sidan-500/30">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Usuarios normales
                        </button>
                        <button type="button"
                            @click="tipoUsuario = 'sistema'"
                            :aria-pressed="tipoUsuario === 'sistema'"
                            :class="tipoUsuario === 'sistema'
                                ? 'border-sidan-500 bg-sidan-500 text-white shadow-sm'
                                : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-sidan-300 hover:bg-sidan-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10'"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-sidan-500/30">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            Personal del sistema
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                        x-text="tipoUsuario === 'normales'
                            ? 'Cuentas con rol Usuario.'
                            : 'Administradores y demás roles internos.'"></p>
                </div>

                <label for="buscarUsuario" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Buscar usuario
                </label>

                <div class="relative">

                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
                        </svg>
                    </div>

                    <input id="buscarUsuario" type="search" x-model="search" @input.debounce.300ms="performSearch()"
                        autocomplete="off" placeholder="Buscar por nombre, apellido, documento o correo..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:bg-white focus:ring-2 focus:ring-sidan-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10">

                    <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-4 w-4 animate-spin text-sidan-500 dark:text-sidan-400" viewBox="0 0 24 24"
                            fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>

                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </div>

                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10"
                x-data="userTable(@js($usersData), '{{ csrf_token() }}')"
                @users-updated.window="users = $event.detail.map(user => ({
                    ...user,
                    imagen_perfil: user.imagen_perfil ?? imagenesIniciales[user.id] ?? null
                }))">
                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">

                        <thead class="bg-slate-50 dark:bg-white/[0.02]">
                            <tr>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Usuario
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Correo
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Rol
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Estado
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Acciones
                                </th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-white/5">

                            {{-- Filtro de roles aplicado a los registros existentes, sin alterar acciones. --}}
                            <template x-for="user in usuariosPorTipo(users)" :key="user.id">

                                <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.02]"
                                    :class="{ 'opacity-60': !user.estado_activo }">

                                    <td class="px-6 py-4">
                                        <div class="flex min-w-[230px] items-center gap-3">

                                            <div class="relative shrink-0">

                                                <img :src="user.imagen_perfil ?
                                                    '{{ asset('storage') }}/' + user.imagen_perfil :
                                                    '{{ asset('images/usuario.png') }}'"
                                                    alt="Foto de perfil"
                                                    class="h-10 w-10 rounded-full border border-slate-200 object-cover dark:border-white/10">

                                                <span
                                                    class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-[#0f172a]"
                                                    :class="user.estado_activo ?
                                                        'bg-sidan-500' :
                                                        'bg-slate-400'"></span>

                                            </div>

                                            <div class="min-w-0">

                                                <p class="truncate font-medium text-slate-900 dark:text-white"
                                                    x-text="`${user.nombres} ${user.apellidos}`.trim() || 'Usuario sin nombre'">
                                                </p>

                                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                                    x-text="user.documento || 'Sin documento'"></p>

                                            </div>

                                        </div>
                                    </td>

                                    <td class="px-6 py-4">

                                        <span class="text-sm text-slate-600 dark:text-slate-300"
                                            x-text="user.correo"></span>

                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <span
                                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium"
                                            :class="user.rol_nombre === 'superadmin' ?
                                                'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-400' :
                                                user.rol_nombre === 'admin' ?
                                                'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400' :
                                                'border-slate-200 bg-slate-100 text-slate-600 dark:border-white/5 dark:bg-slate-700/50 dark:text-slate-300'"
                                            x-text="user.rol_nombre
                                            ? user.rol_nombre.charAt(0).toUpperCase() + user.rol_nombre.slice(1)
                                            : 'Sin rol'"></span>

                                    </td>

                                    <td class="px-6 py-4 text-center">

                                        <span x-show="user.estado_activo"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-sidan-200 bg-sidan-50 px-2.5 py-1 text-xs font-medium text-sidan-600 dark:border-sidan-500/20 dark:bg-sidan-500/10 dark:text-sidan-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-sidan-500"></span>

                                            Activo
                                        </span>

                                        <span x-show="!user.estado_activo"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-white/5 dark:bg-slate-700/50 dark:text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>

                                            Bloqueado
                                        </span>

                                    </td>

                                    <td class="px-6 py-4 text-right">

                                        <div class="flex items-center justify-end gap-2">

                                            <a :href="user.edit_url" title="Editar usuario"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-sidan-300 hover:bg-sidan-50 hover:text-sidan-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-sidan-500/50 dark:hover:bg-sidan-500/10 dark:hover:text-sidan-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <button type="button" @click="toggleStatus(user)"
                                                :title="user.estado_activo ?
                                                    'Bloquear usuario' :
                                                    'Reactivar usuario'"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition"
                                                :class="user.estado_activo ?
                                                    'border-slate-200 bg-white text-slate-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-amber-500/50 dark:hover:bg-amber-500/10 dark:hover:text-amber-400' :
                                                    'border-slate-200 bg-white text-slate-600 hover:border-sidan-300 hover:bg-sidan-50 hover:text-sidan-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-sidan-500/50 dark:hover:bg-sidan-500/10 dark:hover:text-sidan-400'">

                                                <svg x-show="user.estado_activo" class="h-4 w-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>

                                                <svg x-show="!user.estado_activo" class="h-4 w-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2Z" />
                                                </svg>

                                            </button>

                                            <button type="button" @click="banUser(user)" title="Eliminar usuario"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-red-500/50 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                </svg>
                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            </template>

                        </tbody>

                    </table>

                </div>

                <div x-show="usuariosPorTipo(users).length === 0"
                    class="border-t border-slate-200 px-6 py-16 text-center sm:px-10 sm:py-20 dark:border-white/5">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">

                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8Zm13 10v-2a4 4 0 00-3-3.87m-2-7.13a4 4 0 010 7.75" />
                        </svg>

                    </div>

                    <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
                        No encontramos usuarios
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                        No hay usuarios que coincidan con la búsqueda realizada.
                        Prueba con otro nombre, correo o documento.
                    </p>

                </div>

            </div>

        </div>
    </div>

    <script>
        function showAppNotification(type, message) {
            const previousNotification = document.getElementById(
                'app-notification'
            );

            if (previousNotification) {
                previousNotification.remove();
            }

            const isSuccess = type === 'success';

            const notification = document.createElement(
                'div'
            );

            notification.id = 'app-notification';

            notification.className =
                'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';

            const iconColor = isSuccess ?
                'bg-sidan-50 text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-400' :
                'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';

            const borderColor = isSuccess ?
                'border-sidan-200 dark:border-sidan-500/20' :
                'border-red-200 dark:border-red-500/20';

            const progressColor = isSuccess ?
                'bg-sidan-500' :
                'bg-red-500';

            const progressBackground = isSuccess ?
                'bg-sidan-100 dark:bg-sidan-950/50' :
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

                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="${iconPath}"
                        ></path>
                    </svg>

                </div>

                <div class="min-w-0 flex-1">

                    <p class="text-sm font-black text-slate-900 dark:text-white">
                        ${title}
                    </p>

                    <p class="notification-message mt-1 text-sm leading-5 text-slate-600 dark:text-slate-400"></p>

                </div>

            </div>

            <button
                type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar alerta"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18 18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>

            <div class="h-1 w-full ${progressBackground}">

                <div class="notification-progress h-full w-full origin-left ${progressColor}"></div>

            </div>

        </div>
    `;

            notification.querySelector(
                '.notification-message'
            ).textContent = message;

            document.body.appendChild(
                notification
            );

            const progress = notification.querySelector(
                '.notification-progress'
            );

            const closeButton = notification.querySelector(
                '.notification-close'
            );

            let closeTimeout;

            const closeNotification = () => {
                clearTimeout(
                    closeTimeout
                );

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

                    progress.style.transition =
                        'transform 5s linear';

                    progress.style.transform =
                        'scaleX(0)';
                });
            });

            closeTimeout = setTimeout(
                closeNotification,
                5000
            );
        }

        function escapeHtml(value) {
            const element = document.createElement(
                'div'
            );

            element.textContent = value ?? '';

            return element.innerHTML;
        }

        function userTable(usersData, csrfToken) {
            return {
                users: usersData,

                showConfirm(title, message, variant = 'emerald') {
                    if (
                        document.getElementById(
                            'app-confirm-overlay'
                        )
                    ) {
                        return Promise.resolve(
                            false
                        );
                    }

                    return new Promise(resolve => {
                        const overlay =
                            document.createElement(
                                'div'
                            );

                        overlay.id =
                            'app-confirm-overlay';

                        overlay.className =
                            'fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200';

                        const modal =
                            document.createElement(
                                'div'
                            );

                        modal.setAttribute(
                            'role',
                            'dialog'
                        );

                        modal.setAttribute(
                            'aria-modal',
                            'true'
                        );

                        modal.setAttribute(
                            'tabindex',
                            '-1'
                        );

                        modal.className =
                            'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';

                        const variants = {
                            emerald: {
                                icon: 'bg-sidan-50 text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-400',
                                button: 'bg-sidan-500 hover:bg-sidan-500',
                                path: 'M5 13l4 4L19 7'
                            },

                            amber: {
                                icon: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                                button: 'bg-amber-600 hover:bg-amber-500',
                                path: 'M12 9v4m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18Z'
                            },

                            red: {
                                icon: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                                button: 'bg-red-600 hover:bg-red-500',
                                path: 'M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3'
                            }
                        };

                        const style =
                            variants[variant] ??
                            variants.emerald;

                        modal.innerHTML = `
                    <div class="flex items-start gap-4">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${style.icon}">

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="${style.path}"
                                ></path>
                            </svg>

                        </div>

                        <div class="min-w-0 flex-1">

                            <h3 class="confirm-title text-base font-black text-slate-900 dark:text-white"></h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Confirma antes de continuar
                            </p>

                        </div>

                    </div>

                    <div class="confirm-message mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300"></div>

                    <div class="mt-6 flex justify-end gap-3">

                        <button
                            type="button"
                            class="cancel-btn inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            class="confirm-btn inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold text-white transition ${style.button}"
                        >
                            Confirmar
                        </button>

                    </div>
                `;

                        modal.querySelector(
                            '.confirm-title'
                        ).textContent = title;

                        modal.querySelector(
                            '.confirm-message'
                        ).innerHTML = message;

                        overlay.appendChild(
                            modal
                        );

                        document.body.appendChild(
                            overlay
                        );

                        const confirmButton =
                            modal.querySelector(
                                '.confirm-btn'
                            );

                        const cancelButton =
                            modal.querySelector(
                                '.cancel-btn'
                            );

                        let resolved = false;

                        const close = value => {
                            if (resolved) {
                                return;
                            }

                            resolved = true;

                            confirmButton.disabled =
                                true;

                            cancelButton.disabled =
                                true;

                            document.removeEventListener(
                                'keydown',
                                handleKeydown,
                                true
                            );

                            overlay.classList.remove(
                                'opacity-100'
                            );

                            modal.classList.remove(
                                'translate-y-0',
                                'scale-100',
                                'opacity-100'
                            );

                            modal.classList.add(
                                'translate-y-4',
                                'scale-95',
                                'opacity-0'
                            );

                            setTimeout(() => {
                                overlay.remove();

                                resolve(
                                    value
                                );
                            }, 200);
                        };

                        const handleKeydown = event => {
                            if (
                                event.key === 'Enter'
                            ) {
                                event.preventDefault();
                                event.stopPropagation();

                                if (
                                    event.repeat ||
                                    resolved
                                ) {
                                    return;
                                }

                                close(true);
                            }

                            if (
                                event.key === 'Escape'
                            ) {
                                event.preventDefault();
                                event.stopPropagation();

                                if (resolved) {
                                    return;
                                }

                                close(false);
                            }
                        };

                        confirmButton.addEventListener(
                            'click',
                            () => close(true)
                        );

                        cancelButton.addEventListener(
                            'click',
                            () => close(false)
                        );

                        overlay.addEventListener(
                            'click',
                            event => {
                                if (
                                    event.target ===
                                    overlay
                                ) {
                                    close(false);
                                }
                            }
                        );

                        document.addEventListener(
                            'keydown',
                            handleKeydown,
                            true
                        );

                        requestAnimationFrame(() => {
                            overlay.classList.add(
                                'opacity-100'
                            );

                            modal.classList.remove(
                                'translate-y-4',
                                'scale-95',
                                'opacity-0'
                            );

                            modal.classList.add(
                                'translate-y-0',
                                'scale-100',
                                'opacity-100'
                            );

                            modal.focus();
                        });
                    });
                },

                async toggleStatus(user) {
                    const willBeBanned =
                        user.estado_activo;

                    const userName = escapeHtml(
                        `${user.nombres} ${user.apellidos}`.trim()
                    );

                    const titulo = willBeBanned ?
                        'Bloquear usuario' :
                        'Reactivar usuario';

                    const mensaje = willBeBanned ?
                        `
                    Estás a punto de <strong>bloquear</strong> a
                    <strong>${userName}</strong>.<br><br>

                    El usuario será movido a la lista de bloqueados y
                    no podrá iniciar sesión.
                ` :
                        `
                    Estás a punto de <strong>reactivar</strong> a
                    <strong>${userName}</strong>.<br><br>

                    El usuario recuperará el acceso al sistema.
                `;

                    const confirmado =
                        await this.showConfirm(
                            titulo,
                            mensaje,
                            willBeBanned ?
                            'amber' :
                            'emerald'
                        );

                    if (!confirmado) {
                        return;
                    }

                    fetch(
                            user.toggle_url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',

                                    'X-CSRF-TOKEN': csrfToken,

                                    'Accept': 'application/json'
                                }
                            }
                        )
                        .then(
                            response =>
                            response.json()
                        )
                        .then(data => {
                            if (data.success) {
                                user.estado_activo = !willBeBanned;

                                if (willBeBanned) {
                                    this.users =
                                        this.users.filter(
                                            currentUser =>
                                            String(
                                                currentUser.id
                                            ) !==
                                            String(
                                                user.id
                                            )
                                        );
                                }

                                this.showNotification(
                                    'success',
                                    data.message ||
                                    'Estado actualizado correctamente'
                                );

                                return;
                            }

                            this.showNotification(
                                'error',
                                data.message ||
                                'Error al actualizar el estado'
                            );
                        })
                        .catch(() => {
                            this.showNotification(
                                'error',
                                'Error de conexión'
                            );
                        });
                },

                async banUser(user) {
                    const userName = escapeHtml(
                        `${user.nombres} ${user.apellidos}`.trim()
                    );

                    const confirmado =
                        await this.showConfirm(
                            'Eliminar usuario',
                            `
                        Estás a punto de eliminar a
                        <strong>${userName}</strong> del listado principal.<br><br>

                        El usuario será enviado a la sección de
                        <strong>Usuarios Eliminados</strong> y no podrá
                        iniciar sesión.
                    `,
                            'red'
                        );

                    if (!confirmado) {
                        return;
                    }

                    const confirmacionFinal =
                        await this.showConfirm(
                            'Confirmación final',
                            `
                        ¿Estás completamente seguro de eliminar a
                        <strong>${userName}</strong>?<br><br>

                        Esta acción requiere una segunda confirmación.
                    `,
                            'red'
                        );

                    if (!confirmacionFinal) {
                        return;
                    }

                    fetch(
                            user.ban_url, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',

                                    'X-CSRF-TOKEN': csrfToken,

                                    'Accept': 'application/json'
                                }
                            }
                        )
                        .then(
                            response =>
                            response.json()
                        )
                        .then(data => {
                            if (data.success) {
                                this.users =
                                    this.users.filter(
                                        currentUser =>
                                        String(
                                            currentUser.id
                                        ) !==
                                        String(
                                            user.id
                                        )
                                    );

                                this.showNotification(
                                    'success',
                                    data.message ||
                                    'Usuario eliminado correctamente'
                                );

                                return;
                            }

                            this.showNotification(
                                'error',
                                data.message ||
                                'Error al eliminar el usuario'
                            );
                        })
                        .catch(() => {
                            this.showNotification(
                                'error',
                                'Error de conexión'
                            );
                        });
                },

                showNotification(type, message) {
                    showAppNotification(
                        type,
                        message
                    );
                }
            };
        }

        document.addEventListener(
            'DOMContentLoaded',
            function() {
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
            }
        );
    </script>
@endsection
