@php
    $referenciaTipo = $referenciaTipo ?? null;
    $referenciaId = $referenciaId ?? null;
    $referenciaNombre = $referenciaNombre ?? null;

    if (!$referenciaNombre && isset($titulo)) {
        $referenciaNombre = $titulo;
    }

    $observaciones = $observaciones ?? collect();

    $deleteBaseUrl = route(
        'admin.actividades.revision.observaciones.store',
        $actividad
    );
@endphp

<div
    class="observation-box"
    data-observation-box
    data-section="{{ $seccion }}"
    data-delete-base-url="{{ $deleteBaseUrl }}"
    @if ($referenciaTipo)
        data-reference-type="{{ $referenciaTipo }}"
    @endif
    @if ($referenciaId)
        data-reference-id="{{ $referenciaId }}"
    @endif
    @if ($referenciaNombre)
        data-reference-name="{{ $referenciaNombre }}"
    @endif
>
    <div class="observation-header">
        <h4>{{ $titulo ?? 'Observaciones' }}</h4>

        <span
            class="observation-count {{ $observaciones->isEmpty() ? 'hidden' : '' }}"
            data-observation-count="{{ $seccion }}"
        >
            {{ $observaciones->count() }}
        </span>
    </div>

    <div class="saved-observations" data-saved-observations>
        @foreach ($observaciones as $observacion)
            @php
                $esRevisionActual =
                    (int) $observacion->id_revision
                    === (int) $revisionActual->id_revision;
                $numeroRevisionObservacion =
                    $observacion->revision?->numero_revision
                    ?? $revisionActual->numero_revision;
            @endphp

            <div
                class="saved-observation"
                data-observation-row="{{ $observacion->id_observacion }}"
                data-section="{{ $observacion->seccion }}"
                data-historical="{{ $esRevisionActual ? '0' : '1' }}"
            >
                <div class="min-w-0 flex-1">
                    @unless ($esRevisionActual)
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-500 dark:text-amber-300">
                                Pendiente desde revisión #{{ $numeroRevisionObservacion }}
                            </span>
                        </div>
                    @endunless

                    <p>{{ $observacion->observacion }}</p>
                </div>

                <button
                    type="button"
                    class="delete-observation {{ $esRevisionActual ? '' : 'resolve-observation' }}"
                    data-delete-observation
                    data-delete-url="{{ route(
                        'admin.actividades.revision.observaciones.destroy',
                        [
                            $actividad,
                            $observacion,
                        ]
                    ) }}"
                    data-delete-kind="{{ $esRevisionActual ? 'delete' : 'resolve' }}"
                    aria-label="{{ $esRevisionActual ? 'Eliminar observación' : 'Marcar observación como corregida' }}"
                    title="{{ $esRevisionActual ? 'Eliminar observación' : 'Marcar como corregida' }}"
                >
                    {{ $esRevisionActual ? '×' : '✓' }}
                </button>
            </div>
        @endforeach
    </div>

    <div
        class="observation-editor {{ $observaciones->isNotEmpty() ? 'hidden' : '' }}"
        data-observation-editor
    >
        <textarea
            rows="2"
            maxlength="2000"
            placeholder="Escribe una observación concreta..."
            aria-label="Nueva observación"
        ></textarea>

        <button
            type="button"
            class="save-observation"
            data-save-observation
            title="Guardar observación"
            aria-label="Guardar observación"
        >
            ✓
        </button>
    </div>

    <button
        type="button"
        class="add-observation {{ $observaciones->isEmpty() ? 'hidden' : '' }}"
        data-add-observation
    >
        + Agregar otra observación
    </button>
</div>
