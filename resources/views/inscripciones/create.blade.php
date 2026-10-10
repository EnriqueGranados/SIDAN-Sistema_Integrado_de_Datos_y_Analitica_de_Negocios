
@extends('layouts.public')

@section('title', 'Inscripción - SIDAN')

@section('content')
<main class="max-w-2xl mx-auto px-4 py-12">

    <h1 class="text-3xl font-bold mb-4">
        Inscribirme a {{ $actividad->nombre }}
    </h1>

    <p class="mb-6 text-gray-500">
        @if($actividad->participacionConPago())
            Esta inscripción requiere pago.
        @else
            Esta inscripción es gratuita.
        @endif
    </p>

    @if($errors->any())
        <div class="mb-5 rounded-lg border border-red-400 p-4 text-red-600">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('sidan.inscripciones.store', $actividad->slug) }}"
        class="space-y-4 rounded-xl border p-6"
    >
        @csrf

        <div>
            <label for="nombre" class="block mb-1">
                Nombre completo
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                value="{{ old('nombre', $nombre) }}"
                maxlength="200"
                required
                class="w-full rounded-lg border p-3 text-gray-900"
            >
        </div>

        <div>
            <label for="correo" class="block mb-1">
                Correo electrónico
            </label>

            <input
                type="email"
                id="correo"
                name="correo"
                value="{{ old('correo', $correo) }}"
                maxlength="150"
                required
                class="w-full rounded-lg border p-3 text-gray-900"
            >
        </div>

        <div>
            <label for="telefono" class="block mb-1">
                Teléfono (opcional)
            </label>

            <input
                type="text"
                id="telefono"
                name="telefono"
                value="{{ old('telefono') }}"
                maxlength="25"
                class="w-full rounded-lg border p-3 text-gray-900"
            >
        </div>

        @if($actividad->participacionConPago())
            <div class="rounded-lg bg-gray-100 text-gray-900 p-4 flex justify-between">
                <span>Precio de inscripción</span>
                <strong>
                    ${{ number_format((float) $actividad->precio_inscripcion, 2) }}
                </strong>
            </div>
        @endif

        <label class="flex gap-2 items-start">
            <input
                type="checkbox"
                name="confirmacion"
                value="1"
                required
                class="mt-1"
            >

            <span>
                Confirmo que los datos proporcionados son correctos.
            </span>
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-green-700 text-white font-semibold px-5 py-3"
        >
            @if($actividad->participacionConPago())
                Continuar al pago
            @else
                Confirmar inscripción gratuita
            @endif
        </button>
    </form>

</main>
@endsection
