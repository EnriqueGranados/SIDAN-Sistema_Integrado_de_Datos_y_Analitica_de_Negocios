@extends('layouts.navbars')

@section('title', 'Gestión de Roles')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Roles</h1>
            <p class="text-sm text-gray-400 mt-1">Administra los perfiles y permisos del sistema</p>
        </div>

        <a href="{{ route('admin.roles.create') }}"
            class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m8-8H4"></path>
            </svg>
            Crear Nuevo Rol
        </a>
    </div>

    <div class="bg-[#0f172a] border border-white/5 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/[0.02] border-b border-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Nombre del Rol
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Usuarios Asignados
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @forelse($roles as $rol)
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="px-6 py-4">
                                <span
                                    class="px-2.5 py-1 text-xs font-medium rounded-full border
                                    @if($rol->nombre === 'superadmin') bg-purple-500/10 text-purple-400 border-purple-500/20
                                    @elseif($rol->nombre === 'admin') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @else bg-gray-500/10 text-gray-400 border-gray-500/20 @endif">
                                    {{ ucfirst($rol->nombre) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $rol->descripcion }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-300">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                    {{ $rol->usuarios_count }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.roles.edit', $rol) }}"
                                        class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-lg transition"
                                        title="Editar rol">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </a>

                                    @if(!in_array($rol->nombre, ['superadmin', 'admin', 'usuario']))
                                        <form action="{{ route('admin.roles.destroy', $rol) }}"
                                            method="POST"
                                            class="inline role-delete-form"
                                            data-role-name="{{ $rol->nombre }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition"
                                                title="Eliminar rol">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>

                                <p class="text-lg font-medium">No hay roles personalizados</p>
                                <p class="text-sm mt-1">Crea un nuevo rol para comenzar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="px-6 py-4 border-t border-white/5">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function showAppNotification(type, title, message) {
        const previous = document.getElementById('app-notification');

        if (previous) {
            previous.remove();
        }

        const styles = {
            success: {
                iconColor: 'bg-emerald-500/10 text-emerald-400',
                borderColor: 'border-emerald-500/20',
                progressColor: 'bg-emerald-500',
                progressBackground: 'bg-emerald-950/50',
                iconPath: 'M5 13l4 4L19 7'
            },
            error: {
                iconColor: 'bg-red-500/10 text-red-400',
                borderColor: 'border-red-500/20',
                progressColor: 'bg-red-500',
                progressBackground: 'bg-red-950/50',
                iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
            },
            warning: {
                iconColor: 'bg-amber-500/10 text-amber-400',
                borderColor: 'border-amber-500/20',
                progressColor: 'bg-amber-500',
                progressBackground: 'bg-amber-950/50',
                iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
            },
            info: {
                iconColor: 'bg-blue-500/10 text-blue-400',
                borderColor: 'border-blue-500/20',
                progressColor: 'bg-blue-500',
                progressBackground: 'bg-blue-950/50',
                iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            }
        };

        const style = styles[type] || styles.info;
        const notification = document.createElement('div');

        notification.id = 'app-notification';
        notification.className =
            'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';

        notification.innerHTML = `
            <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-[#0f172a] shadow-2xl">
                <div class="flex items-start gap-3 p-4 pr-12">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="${style.iconPath}">
                            </path>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="notification-title text-sm font-black text-white"></p>
                        <p class="notification-message mt-1 text-sm leading-5 text-gray-400 whitespace-pre-line"></p>
                    </div>
                </div>

                <button type="button"
                    class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Cerrar notificación">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="h-1 w-full ${style.progressBackground}">
                    <div class="notification-progress h-full w-full origin-left ${style.progressColor}"></div>
                </div>
            </div>
        `;

        notification.querySelector('.notification-title').textContent = title;
        notification.querySelector('.notification-message').textContent = message;
        document.body.appendChild(notification);

        const progress = notification.querySelector('.notification-progress');
        const closeButton = notification.querySelector('.notification-close');
        let timeout;

        const closeNotification = () => {
            clearTimeout(timeout);

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

        timeout = setTimeout(closeNotification, 5000);
    }

    function translateRoleMessage(message) {
        const translations = {
            'Role created successfully.':
                'El rol fue creado correctamente.',

            'Role updated successfully.':
                'El rol fue actualizado correctamente.',

            'Role deleted successfully.':
                'El rol fue eliminado correctamente.',

            'Role not found.':
                'No se encontró el rol solicitado.',

            'This role cannot be deleted.':
                'Este rol no puede ser eliminado.',

            'The role cannot be deleted because it has assigned users.':
                'No se puede eliminar el rol porque tiene usuarios asignados.',

            'Cannot delete role with assigned users.':
                'No se puede eliminar el rol porque tiene usuarios asignados.',

            'An error occurred while deleting the role.':
                'Ocurrió un error al intentar eliminar el rol.',

            'An error occurred while updating the role.':
                'Ocurrió un error al intentar actualizar el rol.',

            'An error occurred while creating the role.':
                'Ocurrió un error al intentar crear el rol.'
        };

        return translations[message] || message;
    }

    function getRoleNotificationTitle(type, message) {
        const text = message.toLowerCase();

        if (type === 'success') {
            if (
                text.includes('creado') ||
                text.includes('created')
            ) {
                return 'Rol creado correctamente';
            }

            if (
                text.includes('actualizado') ||
                text.includes('updated')
            ) {
                return 'Rol actualizado correctamente';
            }

            if (
                text.includes('eliminado') ||
                text.includes('deleted')
            ) {
                return 'Rol eliminado correctamente';
            }

            return 'Operación realizada correctamente';
        }

        if (
            text.includes('usuarios asignados') ||
            text.includes('assigned users')
        ) {
            return 'No se puede eliminar el rol';
        }

        if (
            text.includes('eliminar') ||
            text.includes('delete')
        ) {
            return 'No se pudo eliminar el rol';
        }

        if (
            text.includes('actualizar') ||
            text.includes('update')
        ) {
            return 'No se pudo actualizar el rol';
        }

        if (
            text.includes('crear') ||
            text.includes('create')
        ) {
            return 'No se pudo crear el rol';
        }

        return 'No se pudo completar la operación';
    }

    function showDeleteConfirm(roleName) {
        return new Promise(resolve => {
            const overlay = document.createElement('div');

            overlay.className =
                'fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300';

            const modal = document.createElement('div');

            modal.className =
                'bg-[#0f172a] border border-white/10 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 scale-95 opacity-0 transition-all duration-300';

            modal.innerHTML = `
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-red-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-white">
                            ¿Eliminar rol?
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Esta acción no se puede deshacer
                        </p>
                    </div>
                </div>

                <div class="text-sm text-gray-300 leading-relaxed mb-6">
                    Estás a punto de eliminar el rol
                    <strong class="role-name text-white"></strong>.
                    Confirma que deseas continuar.
                </div>

                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-6">
                    <p class="text-sm text-red-300">
                        El rol será eliminado permanentemente del sistema.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                        class="cancel-btn px-4 py-2 text-sm font-medium text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition">
                        Cancelar
                    </button>

                    <button type="button"
                        class="confirm-btn px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition flex items-center gap-2">
                        Eliminar rol
                    </button>
                </div>
            `;

            modal.querySelector('.role-name').textContent = roleName;

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');

                modal.classList.remove('scale-95', 'opacity-0');
                modal.classList.add('scale-100', 'opacity-100');
            });

            let resolved = false;

            const close = value => {
                if (resolved) {
                    return;
                }

                resolved = true;

                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');

                modal.classList.remove('scale-100', 'opacity-100');
                modal.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    overlay.remove();
                    resolve(value);
                }, 300);
            };

            modal.querySelector('.cancel-btn')
                .addEventListener('click', () => close(false));

            modal.querySelector('.confirm-btn')
                .addEventListener('click', () => close(true));

            overlay.addEventListener('click', event => {
                if (event.target === overlay) {
                    close(false);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            {
                const message = translateRoleMessage(
                    @json(session('success'))
                );

                showAppNotification(
                    'success',
                    getRoleNotificationTitle('success', message),
                    message
                );
            }
        @endif

        @if (session('error'))
            {
                const message = translateRoleMessage(
                    @json(session('error'))
                );

                showAppNotification(
                    'error',
                    getRoleNotificationTitle('error', message),
                    message
                );
            }
        @endif

        document.querySelectorAll('.role-delete-form').forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault();

                const confirmed = await showDeleteConfirm(
                    form.dataset.roleName
                );

                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection