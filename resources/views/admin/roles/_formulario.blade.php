@php
    $rolActual = $rol ?? null;
    $esSuperadmin = $rolActual?->nombre === 'superadmin';
    $esUsuario = $rolActual?->nombre === 'usuario';
    $permisosBloqueados = $esSuperadmin || $esUsuario;

    $seleccionados = session()->hasOldInput()
        ? old('modulos', [])
        : \App\Support\AccesoModulos::modulosDelRol($rolActual);

    $seleccionados = is_array($seleccionados) ? $seleccionados : [];

    $claseCampo = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white';
@endphp

@if ($errors->any())
    <div role="alert"
        class="rounded-xl bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300">
        <ul class="list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="nombre" class="mb-2 block text-sm font-semibold">
            Nombre del rol
        </label>
        <input
            id="nombre"
            name="nombre"
            type="text"
            maxlength="255"
            value="{{ old('nombre', $rolActual?->nombre) }}"
            placeholder="Ejemplo: secretaria"
            class="{{ $claseCampo }}"
            @readonly($rolActual?->esBase())
            required
        >
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
            {{ $rolActual?->esBase()
                ? 'El nombre de este rol base está protegido.'
                : 'El nombre se guardará en minúsculas.' }}
        </p>
    </div>

    <div>
        <label for="descripcion" class="mb-2 block text-sm font-semibold">
            Descripción
        </label>
        <textarea
            id="descripcion"
            name="descripcion"
            rows="3"
            maxlength="2000"
            placeholder="Describí la función de este rol."
            class="{{ $claseCampo }}"
            required
        >{{ old('descripcion', $rolActual?->descripcion) }}</textarea>
    </div>
</div>

<fieldset class="border-t border-slate-200 pt-5 dark:border-white/10">
    <legend class="px-2 text-lg font-bold">Acceso a módulos</legend>

    <p class="mb-5 mt-2 text-sm text-slate-500 dark:text-slate-400">
        Cada módulo seleccionado permite utilizar todas sus funciones,
        respetando las validaciones del sistema.
    </p>

    @if ($esSuperadmin)
        <div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
            El superadministrador tiene acceso completo, incluyendo Roles del Sistema.
        </div>
    @elseif ($esUsuario)
        <div class="mb-5 rounded-xl bg-slate-100 p-4 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300">
            El rol usuario conserva el portal público, sus inscripciones y sus órdenes.
            Para habilitar módulos administrativos, creá un rol personalizado.
        </div>
    @else
        <div class="mb-4 flex flex-wrap gap-3">
            <button type="button" data-seleccionar-modulos="todos"
                class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                Seleccionar todos
            </button>
            <button type="button" data-seleccionar-modulos="ninguno"
                class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 dark:bg-white/5 dark:text-slate-300">
                Quitar selección
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        @foreach ($modulosDisponibles as $clave => $modulo)
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-400 dark:border-white/10 dark:bg-white/5">
                <input
                    type="checkbox"
                    name="modulos[]"
                    value="{{ $clave }}"
                    class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    @checked($esSuperadmin || (!$esUsuario && in_array($clave, $seleccionados, true)))
                    @disabled($permisosBloqueados)
                >
                <span>
                    <span class="block text-sm font-bold">{{ $modulo['nombre'] }}</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                        {{ $modulo['descripcion'] }}
                    </span>
                </span>
            </label>
        @endforeach
    </div>

    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
        Roles del Sistema está reservado al superadministrador.
        Un rol personalizado sin módulos no podrá entrar al panel administrativo.
    </p>
</fieldset>

<div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5 dark:border-white/10">
    <a href="{{ route('admin.roles.index') }}"
        class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold dark:border-white/10">
        Cancelar
    </a>
    <button type="submit"
        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">
        {{ $rolActual ? 'Guardar cambios' : 'Crear rol' }}
    </button>
</div>

@push('scripts')
    <script>
        // Cambiar únicamente la selección visual de los módulos del formulario.
        document.querySelectorAll('[data-seleccionar-modulos]').forEach(button => {
            button.addEventListener('click', () => {
                const form = button.closest('form');
                const checked = button.dataset.seleccionarModulos === 'todos';

                form.querySelectorAll('input[name="modulos[]"]:not(:disabled)')
                    .forEach(input => {
                        input.checked = checked;
                    });
            });
        });
    </script>
@endpush