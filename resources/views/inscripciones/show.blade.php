
@extends('layouts.public')

@section('title', 'Mi inscripción - SIDAN')

@section('content')
<main class="max-w-2xl mx-auto px-4 py-12">

    <h1 class="text-3xl font-bold mb-6">
        Estado de mi inscripción
    </h1>

    <div class="rounded-xl border p-6 space-y-3">

        <p>
            <strong>Actividad:</strong>
            {{ $inscripcion->actividad->nombre }}
        </p>

        <p>
            <strong>Inscripción:</strong>
            #{{ $inscripcion->id_inscripcion }}
        </p>

        <p>
            <strong>Participante:</strong>
            {{ $inscripcion->nombre_participante }}
        </p>

        <p>
            <strong>Estado:</strong>
            {{ str_replace('_', ' ', $inscripcion->estado) }}
        </p>

        @if($inscripcion->estado === 'confirmada')
            <p class="text-green-600 font-semibold">
                Tu inscripción está confirmada.
            </p>
        @endif

        @if($inscripcion->estado === 'confirmada_prueba')
            <p class="text-amber-600">
                Inscripción confirmada en ambiente de pruebas.
            </p>
        @endif

        @if($inscripcion->orden)
            <a
                href="{{ route('sidan.ordenes.show', $inscripcion->orden->id_orden) }}"
                class="underline text-green-700"
            >
                Consultar orden
            </a>
        @endif

    </div>

</main>
@endsection
