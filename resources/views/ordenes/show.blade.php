
@extends('layouts.public')

@section('title', 'Mi orden - SIDAN')

@section('content')
<main class="max-w-2xl mx-auto px-4 py-12">

    <h1 class="text-3xl font-bold mb-6">
        Orden #{{ $orden->id_orden }}
    </h1>

    <div class="rounded-xl border p-6 space-y-4">

        @if($errors->any())
            <div class="rounded-lg bg-red-50 text-red-700 p-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p>
            <strong>Actividad:</strong>
            {{ $orden->actividad->nombre }}
        </p>

        <p>
            <strong>Estado:</strong>
            {{ str_replace('_', ' ', $orden->estado) }}
        </p>

        <div class="border-t pt-4 space-y-2">
            @foreach($orden->detalles as $detalle)
                <div class="flex justify-between gap-4">
                    <span>
                        {{ $detalle->nombre_concepto }}
                        ({{ $detalle->cantidad }})
                    </span>

                    <span>
                        ${{ number_format((float) $detalle->subtotal, 2) }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between border-t pt-4 text-xl">
            <strong>Total</strong>

            <strong>
                ${{ number_format((float) $orden->total, 2) }}
            </strong>
        </div>

        @if(
            $orden->estado === 'pendiente_pago' &&
            (!$orden->expira_en || $orden->expira_en->isFuture())
        )
            <div class="rounded-lg bg-amber-50 text-amber-900 p-4">
                La inscripción está pendiente de pago.

                @if($orden->expira_en)
                    <p>
                        Reserva hasta:
                        {{ $orden->expira_en->format('d/m/Y H:i') }}
                    </p>
                @endif
            </div>

            <form
                method="POST"
                action="{{ route('sidan.pagos.iniciar', $orden->id_orden) }}"
            >
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-lg bg-green-700 text-white font-semibold p-3"
                >
                    Pagar con Wompi
                </button>
            </form>

        @elseif($orden->estado === 'pagada')
            <div class="rounded-lg bg-green-50 text-green-800 p-4">
                Pago verificado. Tu inscripción está confirmada.
            </div>

        @elseif($orden->estado === 'pagada_prueba')
            <div class="rounded-lg bg-blue-50 text-blue-800 p-4">
                Pago de prueba verificado.
                No representa recaudación real.
            </div>

        @elseif($orden->estado === 'revision_cupo')
            <div class="rounded-lg bg-amber-50 text-amber-900 p-4">
                El pago está registrado, pero el cupo
                requiere revisión administrativa.
            </div>

        @else
            <div class="rounded-lg bg-gray-100 text-gray-900 p-4">
                Esta orden no acepta nuevos pagos.
            </div>
        @endif

        @if($orden->id_inscripcion)
            <a
                href="{{ route('sidan.inscripciones.show', $orden->id_inscripcion) }}"
                class="inline-block underline text-green-700"
            >
                Consultar inscripción
            </a>
        @endif

    </div>

</main>
@endsection
