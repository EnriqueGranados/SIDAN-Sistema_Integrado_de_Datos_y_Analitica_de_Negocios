<article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.15em] text-sidan-500">Posición {{ $espacio->orden }}</p>
            <h3 class="mt-1 font-black text-sidan-900 dark:text-white">{{ $espacio->nombre }}</h3>
        </div>

        @if ($espacio->actividad)
            <form method="POST" action="{{ route('admin.welcome.espacios.quitar', $espacio) }}" onsubmit="return confirm('¿Quitar esta actividad de este recuadro?')">
                @csrf
                @method('DELETE')

                <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-xl font-black text-red-500 transition hover:bg-red-500 hover:text-white dark:border-red-500/20 dark:bg-red-500/10">
                    ×
                </button>
            </form>
        @endif
    </div>

    @if ($espacio->actividad)
        <img src="{{ $espacio->actividad->imagen_welcome }}" alt="{{ $espacio->actividad->nombre }}" class="h-44 w-full object-cover">

        <div class="p-5">
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                {{ $espacio->actividad->categoria?->nombre ?? 'Sin categoría' }}
            </span>

            <h4 class="mt-3 line-clamp-2 text-lg font-black text-sidan-900 dark:text-white">
                {{ $espacio->actividad->nombre }}
            </h4>
        </div>
    @else
        <div class="flex h-56 items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border-2 border-dashed border-slate-300 text-3xl text-slate-400 dark:border-white/20">+</div>
                <p class="mt-3 text-sm font-bold text-slate-500 dark:text-slate-400">Espacio disponible</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.welcome.espacios.asignar', $espacio) }}" class="border-t border-slate-200 p-5 dark:border-white/10">
        @csrf
        @method('PUT')

        <select name="id_actividad" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
            <option value="">Selecciona una actividad...</option>

            @foreach ($actividadesDisponibles as $actividad)
                <option value="{{ $actividad->id_actividad }}" @selected($espacio->id_actividad == $actividad->id_actividad)>
                    {{ $actividad->nombre }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="mt-3 w-full rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-green-600">
            {{ $espacio->actividad ? 'Reemplazar' : 'Agregar' }}
        </button>
    </form>
</article>