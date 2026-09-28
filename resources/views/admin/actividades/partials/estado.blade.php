@if ($actividad->estado_publicacion === 'borrador')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
        Configuración pendiente
    </span>

@elseif ($actividad->estado_publicacion === 'pendiente_revision')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/20 bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
        En revisión
    </span>

@elseif ($actividad->estado_publicacion === 'cambios_solicitados')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-500/20 bg-orange-500/10 px-2.5 py-1 text-xs font-semibold text-orange-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-orange-400"></span>
        Cambios solicitados
    </span>

@elseif ($actividad->estado_publicacion === 'aprobada')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
        Aprobada
    </span>

@elseif ($actividad->estado_publicacion === 'rechazada')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"></span>
        Rechazada
    </span>

@elseif ($actividad->estado_publicacion === 'publicada')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></span>
        Publicada
    </span>

@elseif ($actividad->estado_publicacion === 'retirada')
    <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-500/20 bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gray-400"></span>
        Retirada
    </span>

@else
    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-gray-400">
        {{ ucfirst(str_replace('_', ' ', $actividad->estado_publicacion)) }}
    </span>
@endif